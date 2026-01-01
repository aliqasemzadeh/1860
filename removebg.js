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
        const threshold = 245; // حد آستانه برای تشخیص سفید (0-255)
        const colorThreshold = 30; // حد تفاوت رنگ برای تشخیص سفید

        // پردازش pixel‌ها و تبدیل سفید به transparent
        for (let i = 0; i < data.length; i += channels) {
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            const a = data[i + 3];

            // محاسبه روشنایی (brightness)
            const brightness = (r + g + b) / 3;

            // محاسبه تفاوت بین رنگ‌ها (برای تشخیص سفید خالص)
            const colorDiff = Math.max(r, g, b) - Math.min(r, g, b);

            // تشخیص سفید: روشنایی بالا و تفاوت رنگ کم
            const isWhite = brightness >= threshold && colorDiff <= colorThreshold;

            // اگر سفید باشد، alpha را 0 می‌کنیم (transparent)
            if (isWhite) {
                data[i + 3] = 0; // alpha channel
            }
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
