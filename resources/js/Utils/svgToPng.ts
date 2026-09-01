const dangerousElements = 'script, foreignObject, iframe, object, embed, audio, video';
const unsafeStyle = /(?:javascript:|expression\s*\(|@import|url\s*\(\s*["']?(?:https?:|\/\/|data:text))/i;

export const isSvgFile = (file: File) =>
    file.type === 'image/svg+xml' || file.name.toLowerCase().endsWith('.svg');

export async function svgToPng(file: File): Promise<File> {
    const source = await file.text();
    const svgDocument = new DOMParser().parseFromString(source, 'image/svg+xml');
    const root = svgDocument.documentElement;

    if (root.localName !== 'svg' || svgDocument.querySelector('parsererror')) {
        throw new Error(`${file.name} is not a valid SVG image.`);
    }

    sanitizeSvg(root);

    const sanitized = new XMLSerializer().serializeToString(svgDocument);
    const blob = new Blob([sanitized], { type: 'image/svg+xml' });
    const objectUrl = URL.createObjectURL(blob);

    try {
        const image = await loadImage(objectUrl);
        const { width, height } = renderSize(root, image);
        const canvas = window.document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const context = canvas.getContext('2d');

        if (!context) {
            throw new Error('This browser could not prepare the SVG image.');
        }

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, width, height);
        context.drawImage(image, 0, 0, width, height);

        const png = await canvasToBlob(canvas);
        const pngName = file.name.replace(/\.svg$/i, '') + '.png';

        return new File([png], pngName, { type: 'image/png', lastModified: file.lastModified });
    } finally {
        URL.revokeObjectURL(objectUrl);
    }
}

function sanitizeSvg(root: Element) {
    root.querySelectorAll(dangerousElements).forEach((element) => element.remove());

    [root, ...Array.from(root.querySelectorAll('*'))].forEach((element) => {
        Array.from(element.attributes).forEach((attribute) => {
            const name = attribute.name.toLowerCase();
            const value = attribute.value.trim();

            if (name.startsWith('on')) {
                element.removeAttribute(attribute.name);

                return;
            }

            if ((name === 'href' || name.endsWith(':href')) && value !== '' && !value.startsWith('#') && !/^data:image\/(?:png|jpeg|webp|gif);base64,/i.test(value)) {
                element.removeAttribute(attribute.name);

                return;
            }

            if ((name === 'style' || name === 'class') && unsafeStyle.test(value)) {
                element.removeAttribute(attribute.name);
            }
        });
    });
}

function loadImage(source: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('The SVG image could not be rendered.'));
        image.src = source;
    });
}

function renderSize(root: Element, image: HTMLImageElement) {
    const viewBox = root.getAttribute('viewBox')
        ?.trim()
        .split(/[\s,]+/)
        .map(Number);
    const viewBoxWidth = viewBox?.length === 4 && viewBox[2] > 0 ? viewBox[2] : null;
    const viewBoxHeight = viewBox?.length === 4 && viewBox[3] > 0 ? viewBox[3] : null;
    const sourceWidth = dimension(root.getAttribute('width')) || viewBoxWidth || image.naturalWidth || 1200;
    const sourceHeight = dimension(root.getAttribute('height')) || viewBoxHeight || image.naturalHeight || 1200;
    const longestSide = Math.max(sourceWidth, sourceHeight);
    const targetLongestSide = Math.min(2400, Math.max(1400, longestSide));
    const scale = targetLongestSide / longestSide;

    return {
        width: Math.max(1, Math.round(sourceWidth * scale)),
        height: Math.max(1, Math.round(sourceHeight * scale)),
    };
}

function dimension(value: string | null): number | null {
    if (!value || value.includes('%')) {
        return null;
    }

    const parsed = Number.parseFloat(value);

    return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
}

function canvasToBlob(canvas: HTMLCanvasElement): Promise<Blob> {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);

                return;
            }

            reject(new Error('The SVG image could not be converted to PNG.'));
        }, 'image/png');
    });
}
