import fs from "node:fs/promises";
import path from "node:path";
import sharp from "sharp";

async function removebg(inputPath, outPath) {
    // بررسی وجود فایل
    try {
        await fs.access(inputPath);
    } catch (error) {
        throw new Error(`Input file not found: ${inputPath}`);
    }

    try {
        // خواندن تصویر و دریافت raw pixel data
        const image = sharp(inputPath);
        const { data, info } = await image
            .ensureAlpha() // اطمینان از وجود alpha channel
            .raw()
            .toBuffer({ resolveWithObject: true });

        const channels = info.channels; // معمولاً 4 (RGBA)
        const width = info.width;
        const height = info.height;
        
        // پارامترهای بهبود یافته برای دقت بیشتر
        const whiteThreshold = 240; // حد آستانه پایه برای تشخیص سفید (0-255)
        const colorTolerance = 25; // حد تفاوت رنگ برای تشخیص سفید خالص
        const minBrightness = 220; // حداقل روشنایی برای در نظر گیری
        const featherRadius = 2; // شعاع feather برای لبه‌های نرم
        
        // تابع محاسبه فاصله اقلیدسی از رنگ سفید
        function distanceFromWhite(r, g, b) {
            return Math.sqrt(
                Math.pow(255 - r, 2) + 
                Math.pow(255 - g, 2) + 
                Math.pow(255 - b, 2)
            );
        }
        
        // تابع محاسبه روشنایی وزنی (perceived brightness)
        function perceivedBrightness(r, g, b) {
            // استفاده از فرمول استاندارد برای روشنایی درک شده
            return 0.299 * r + 0.587 * g + 0.114 * b;
        }
        
        // تابع تشخیص سفید با دقت بالا
        function isWhitePixel(r, g, b, a) {
            // اگر alpha از قبل transparent باشد، نادیده بگیر
            if (a === 0) return false;
            
            // محاسبه روشنایی درک شده
            const brightness = perceivedBrightness(r, g, b);
            
            // اگر روشنایی خیلی کم باشد، قطعاً سفید نیست
            if (brightness < minBrightness) return false;
            
            // محاسبه تفاوت بین کانال‌های رنگی
            const colorDiff = Math.max(r, g, b) - Math.min(r, g, b);
            
            // محاسبه فاصله از رنگ سفید خالص
            const whiteDistance = distanceFromWhite(r, g, b);
            
            // تشخیص سفید: روشنایی بالا، تفاوت رنگ کم، و فاصله کم از سفید
            const isBrightEnough = brightness >= whiteThreshold;
            const isNeutralColor = colorDiff <= colorTolerance;
            const isCloseToWhite = whiteDistance <= (255 - whiteThreshold) * 1.5;
            
            return isBrightEnough && isNeutralColor && isCloseToWhite;
        }
        
        // پردازش pixel‌ها و تبدیل سفید به transparent
        for (let i = 0; i < data.length; i += channels) {
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            const a = data[i + 3];
            
            if (isWhitePixel(r, g, b, a)) {
                data[i + 3] = 0; // alpha channel را transparent می‌کنیم
            }
        }
        
        // پردازش لبه‌ها برای حذف بهتر (anti-aliasing)
        if (featherRadius > 0) {
            const newData = Buffer.from(data);
            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    const idx = (y * width + x) * channels;
                    const r = data[idx];
                    const g = data[idx + 1];
                    const b = data[idx + 2];
                    const a = data[idx + 3];
                    
                    // اگر pixel فعلی transparent نیست اما نزدیک به transparent است
                    if (a > 0 && a < 255) {
                        // بررسی همسایه‌ها
                        let transparentNeighbors = 0;
                        let totalNeighbors = 0;
                        
                        for (let dy = -featherRadius; dy <= featherRadius; dy++) {
                            for (let dx = -featherRadius; dx <= featherRadius; dx++) {
                                const nx = x + dx;
                                const ny = y + dy;
                                
                                if (nx >= 0 && nx < width && ny >= 0 && ny < height) {
                                    totalNeighbors++;
                                    const neighborIdx = (ny * width + nx) * channels;
                                    if (data[neighborIdx + 3] === 0) {
                                        transparentNeighbors++;
                                    }
                                }
                            }
                        }
                        
                        // اگر بیشتر همسایه‌ها transparent هستند، این pixel را هم transparent کن
                        if (transparentNeighbors > totalNeighbors * 0.5) {
                            newData[idx + 3] = 0;
                        }
                    }
                    
                    // اگر pixel سفید است اما همسایه‌هایش transparent هستند، آن را هم transparent کن
                    if (a > 0 && isWhitePixel(r, g, b, a)) {
                        let hasTransparentNeighbor = false;
                        for (let dy = -1; dy <= 1; dy++) {
                            for (let dx = -1; dx <= 1; dx++) {
                                if (dx === 0 && dy === 0) continue;
                                const nx = x + dx;
                                const ny = y + dy;
                                
                                if (nx >= 0 && nx < width && ny >= 0 && ny < height) {
                                    const neighborIdx = (ny * width + nx) * channels;
                                    if (data[neighborIdx + 3] === 0) {
                                        hasTransparentNeighbor = true;
                                        break;
                                    }
                                }
                            }
                            if (hasTransparentNeighbor) break;
                        }
                        
                        if (hasTransparentNeighbor) {
                            newData[idx + 3] = 0;
                        }
                    }
                }
            }
            // کپی داده‌های جدید
            newData.copy(data);
        }

        // ساخت تصویر جدید با pixel data پردازش شده
        const pngBuffer = await sharp(data, {
            raw: {
                width: width,
                height: height,
                channels: channels,
            },
        })
            .png({
                compressionLevel: 9,
                quality: 100,
                palette: false, // برای حفظ transparency
            })
            .toBuffer();

        await fs.writeFile(outPath, pngBuffer);
        return outPath;
    } catch (error) {
        throw new Error(`Background removal failed: ${error.message}`);
    }
}

const inputFile = process.argv[2];
if (!inputFile) {
    console.error("Usage: node removebg.js <input.(jpg|png|...)> [output.png]");
    process.exit(1);
}

const outputFile =
    process.argv[3] ??
    path.join(path.dirname(inputFile), `${path.parse(inputFile).name}.png`);

removebg(inputFile, outputFile)
    .then((p) => console.log("OK:", p))
    .catch((e) => {
        console.error("Failed:", e?.message ?? e);
        if (e.stack) {
            console.error("Stack:", e.stack);
        }
        process.exit(2);
    });
