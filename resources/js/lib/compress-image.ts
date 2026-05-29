const DEFAULT_MAX_WIDTH = 1600;
const DEFAULT_MAX_HEIGHT = 1600;
const DEFAULT_QUALITY = 0.82;
const DEFAULT_MAX_BYTES = 400 * 1024;

export interface CompressImageOptions {
    maxWidth?: number;
    maxHeight?: number;
    quality?: number;
    maxBytes?: number;
}

/**
 * Сжимает фото с камеры телефона перед отправкой формы регистрации.
 */
export async function compressImageFile(file: File, options: CompressImageOptions = {}): Promise<File> {
    if (!file.type.startsWith('image/')) {
        return file;
    }

    const maxBytes = options.maxBytes ?? DEFAULT_MAX_BYTES;
    if (file.size <= maxBytes && file.type === 'image/jpeg') {
        return file;
    }

    const maxWidth = options.maxWidth ?? DEFAULT_MAX_WIDTH;
    const maxHeight = options.maxHeight ?? DEFAULT_MAX_HEIGHT;
    let quality = options.quality ?? DEFAULT_QUALITY;

    const bitmap = await createImageBitmap(file);
    let { width, height } = bitmap;
    const scale = Math.min(1, maxWidth / width, maxHeight / height);
    width = Math.round(width * scale);
    height = Math.round(height * scale);

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        bitmap.close();
        return file;
    }

    ctx.drawImage(bitmap, 0, 0, width, height);
    bitmap.close();

    const encode = (q: number) =>
        new Promise<Blob>((resolve, reject) => {
            canvas.toBlob(
                (blob) => (blob ? resolve(blob) : reject(new Error('Не удалось сжать изображение'))),
                'image/jpeg',
                q,
            );
        });

    let blob = await encode(quality);
    while (blob.size > maxBytes && quality > 0.5) {
        quality -= 0.1;
        blob = await encode(quality);
    }

    const baseName = file.name.replace(/\.[^.]+$/i, '') || 'image';
    return new File([blob], `${baseName}.jpg`, {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}
