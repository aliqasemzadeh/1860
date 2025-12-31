import fs from "node:fs/promises";
import path from "node:path";
import sharp from "sharp";
import { removeBackground } from "@imgly/background-removal-node";

async function convert(inputPath, outPath) {
    // بررسی وجود فایل
    try {
        await fs.access(inputPath);
    } catch (error) {
        throw new Error(`Input file not found: ${inputPath}`);
    }

    // removeBackground با مسیر فایل (string) کار می‌کند و یک Blob برمی‌گرداند
    let pngTransparentBlob;
    try {
        pngTransparentBlob = await removeBackground(inputPath);
    } catch (error) {
        throw new Error(`Background removal failed: ${error.message}`);
    }

    // تبدیل Blob به Buffer
    // removeBackground همیشه یک Blob برمی‌گرداند
    let buffer;
    if (pngTransparentBlob instanceof Blob) {
        buffer = Buffer.from(await pngTransparentBlob.arrayBuffer());
    } else if (Buffer.isBuffer(pngTransparentBlob)) {
        buffer = pngTransparentBlob;
    } else if (pngTransparentBlob instanceof Uint8Array) {
        buffer = Buffer.from(pngTransparentBlob);
    } else if (pngTransparentBlob instanceof ArrayBuffer) {
        buffer = Buffer.from(pngTransparentBlob);
    } else {
        throw new Error(`Unexpected return type from removeBackground: ${pngTransparentBlob.constructor.name}`);
    }

    // بررسی اینکه buffer خالی نیست
    if (!buffer || buffer.length === 0) {
        throw new Error("Background removal returned empty buffer");
    }

    // تبدیل به WebP شفاف
    let webp;
    try {
        webp = await sharp(buffer, { 
            failOn: 'none' // برای جلوگیری از خطا در فرمت‌های غیرمعمول
        })
            .webp({
                quality: 90,
                alphaQuality: 100,
                effort: 6,       // 0..6 (بیشتر=بهینه‌تر)
                lossless: false, // اگر خواستی بدون افت: true
            })
            .toBuffer();
    } catch (error) {
        throw new Error(`WebP conversion failed: ${error.message}. Buffer length: ${buffer.length}, First bytes: ${buffer.slice(0, 8).toString('hex')}`);
    }

    await fs.writeFile(outPath, webp);
    return outPath;
}

const inputFile = process.argv[2];
if (!inputFile) {
    console.error("Usage: node bg2webp.mjs <input.(jpg|png|...)> [output.webp]");
    process.exit(1);
}

const outputFile =
    process.argv[3] ??
    path.join(path.dirname(inputFile), `${path.parse(inputFile).name}.webp`);

convert(inputFile, outputFile)
    .then((p) => console.log("OK:", p))
    .catch((e) => {
        console.error("Failed:", e?.message ?? e);
        if (e.stack) {
            console.error("Stack:", e.stack);
        }
        process.exit(2);
    });
