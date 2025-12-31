import { removeBackground } from "@imgly/background-removal-node";
import fs from "node:fs/promises";
import path from "node:path";

async function test() {
    const inputFile = "1.jpg";
    
    console.log("Testing removeBackground with different input types...");
    
    // Test 1: File path as string
    try {
        console.log("\n1. Testing with file path (string):", inputFile);
        const result1 = await removeBackground(inputFile);
        console.log("✓ Success! Result type:", result1.constructor.name);
        console.log("  Result is Blob:", result1 instanceof Blob);
        if (result1 instanceof Blob) {
            const buffer = Buffer.from(await result1.arrayBuffer());
            console.log("  Buffer length:", buffer.length);
            await fs.writeFile("test-output-1.png", buffer);
            console.log("  Saved to test-output-1.png");
        }
    } catch (error) {
        console.log("✗ Failed:", error.message);
    }
    
    // Test 2: File URL
    try {
        console.log("\n2. Testing with file:// URL");
        const fileUrl = `file://${path.resolve(inputFile)}`;
        const result2 = await removeBackground(fileUrl);
        console.log("✓ Success! Result type:", result2.constructor.name);
        if (result2 instanceof Blob) {
            const buffer = Buffer.from(await result2.arrayBuffer());
            await fs.writeFile("test-output-2.png", buffer);
            console.log("  Saved to test-output-2.png");
        }
    } catch (error) {
        console.log("✗ Failed:", error.message);
    }
    
    // Test 3: Buffer
    try {
        console.log("\n3. Testing with Buffer");
        const input = await fs.readFile(inputFile);
        const result3 = await removeBackground(input);
        console.log("✓ Success! Result type:", result3.constructor.name);
        if (result3 instanceof Blob) {
            const buffer = Buffer.from(await result3.arrayBuffer());
            await fs.writeFile("test-output-3.png", buffer);
            console.log("  Saved to test-output-3.png");
        }
    } catch (error) {
        console.log("✗ Failed:", error.message);
    }
    
    // Test 4: Uint8Array
    try {
        console.log("\n4. Testing with Uint8Array");
        const input = await fs.readFile(inputFile);
        const inputArray = new Uint8Array(input);
        const result4 = await removeBackground(inputArray);
        console.log("✓ Success! Result type:", result4.constructor.name);
        if (result4 instanceof Blob) {
            const buffer = Buffer.from(await result4.arrayBuffer());
            await fs.writeFile("test-output-4.png", buffer);
            console.log("  Saved to test-output-4.png");
        }
    } catch (error) {
        console.log("✗ Failed:", error.message);
    }
}

test().catch(console.error);

