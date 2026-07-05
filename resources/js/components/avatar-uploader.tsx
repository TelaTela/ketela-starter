import { useLang } from '@erag/lang-sync-inertia/react';
import { ImagePlus, ZoomIn, ZoomOut } from 'lucide-react';
import React, {
    forwardRef,
    useImperativeHandle,
    useRef,
    useState,
    type PointerEvent as ReactPointerEvent,
    type WheelEvent as ReactWheelEvent,
} from 'react';
import { Button } from './ui/button';
import InputError from './input-error';

/** Display diameter of the crop circle. */
const CROP_SIZE = 224;

/** Resolution of the final exported squre image. */
const OUTPUT_SIZE = 512;

const MIN_ZOOM = 1;
const MAX_ZOOM = 3;

const MAX_FILE_SIZE = 5 * 1024 * 1024;
const ACCEPTED_FILETYPES = ['image/jpeg', 'image/png', 'image/webp'];

type AvatarUploaderProps = {
    currentAvatarUrl: string | null;
    name: string;
    error?: string;
};

export type AvatarUploaderHandle = {
    /**
     * Clears the staged file so a later, unrelated form submission
     * (e.g. saving just a name change) doesn't resend a previously
     * uploaded avatar and re-trigger conversion regeneration.
     */
    clearStagedFile: () => void;
};

function clampZoom(zoom: number): number {
    return Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, zoom));
}

const AvatarUploader = forwardRef<AvatarUploaderHandle, AvatarUploaderProps>(
    function AvatarUploader({ currentAvatarUrl, name, error }, ref) {
        const { __ } = useLang();

        const [isCropping, setIsCropping] = useState(false);
        const [imageSrc, setImageSrc] = useState<string | null>(null);
        const [zoom, setZoom] = useState(MIN_ZOOM);
        const [position, setPosition] = useState({ x: 0, y: 0 });
        const [previewUrl, setPreviewUrl] = useState<string | null>(
            currentAvatarUrl,
        );
        const [clientError, setClientError] = useState<string | null>(null);

        const imgRef = useRef<HTMLImageElement>(null);
        const rawFileInputRef = useRef<HTMLInputElement>(null);
        const croppedFileInputRef = useRef<HTMLInputElement>(null);
        const draggingRef = useRef<{
            startX: number;
            startY: number;
            originX: number;
            originY: number;
        } | null>(null);
        const imgMetaRef = useRef({ width: 0, height: 0, baseScale: 1 });

        useImperativeHandle(ref, () => ({
            clearStagedFile() {
                if (croppedFileInputRef.current) {
                    croppedFileInputRef.current.files =
                        new DataTransfer().files;
                }
            },
        }));

        function clampPosition(
            pos: { x: number; y: number },
            currentZoom: number,
        ) {
            const { width, height, baseScale } = imgMetaRef.current;
            const scale = baseScale * currentZoom;
            const maxX = Math.max(0, (width * scale - CROP_SIZE) / 2);
            const maxY = Math.max(0, (height * scale - CROP_SIZE) / 2);

            return {
                x: Math.min(maxX, Math.max(-maxX, pos.x)),
                y: Math.min(maxY, Math.max(-maxY, pos.y)),
            };
        }

        function handleFileSelected(file: File) {
            if (!ACCEPTED_FILETYPES.includes(file.type)) {
                setClientError(
                    __('components/avatar-uploader.errors.invalid_type'),
                );
                return;
            }

            if (file.size > MAX_FILE_SIZE) {
                setClientError(
                    __('components/avatar-uploader.errors.too_large'),
                );
                return;
            }

            setClientError(null);

            if (imageSrc) {
                URL.revokeObjectURL(imageSrc);
            }

            setZoom(MIN_ZOOM);
            setPosition({ x: 0, y: 0 });
            setImageSrc(URL.createObjectURL(file));
            setIsCropping(true);
        }

        function onImageLoad() {
            const img = imgRef.current;
            if (!img) {
                return;
            }

            imgMetaRef.current = {
                width: img.naturalWidth,
                height: img.naturalHeight,
                baseScale: Math.max(
                    CROP_SIZE / img.naturalWidth,
                    CROP_SIZE / img.naturalHeight,
                ),
            };
        }

        function onPointerDown(e: ReactPointerEvent<HTMLDivElement>) {
            draggingRef.current = {
                startX: e.clientX,
                startY: e.clientY,
                originX: position.x,
                originY: position.y,
            };
            e.currentTarget.setPointerCapture(e.pointerId);
        }

        function onPointerMove(e: ReactPointerEvent<HTMLDivElement>) {
            if (!draggingRef.current) {
                return;
            }

            const dx = e.clientX - draggingRef.current.startX;
            const dy = e.clientY - draggingRef.current.startY;

            setPosition(
                clampPosition(
                    {
                        x: draggingRef.current.originX + dx,
                        y: draggingRef.current.originY + dy,
                    },
                    zoom,
                ),
            );
        }

        function onPointerUp() {
            draggingRef.current = null;
        }

        function onWheel(e: ReactWheelEvent<HTMLDivElement>) {
            e.preventDefault();
            const next = clampZoom(zoom + (e.deltaY > 0 ? -0.05 : 0.05));
            setZoom(next);
            setPosition((p) => clampPosition(p, next));
        }

        function onZoomChange(value: number) {
            const next = clampZoom(value);
            setZoom(next);
            setPosition((p) => clampPosition(p, next));
        }

        function cancelCrop() {
            if (imageSrc) {
                URL.revokeObjectURL(imageSrc);
            }

            setImageSrc(null);
            setIsCropping(false);
        }

        function confirmCrop() {
            const img = imgRef.current;
            const canvas = document.createElement('canvas');
            canvas.width = OUTPUT_SIZE;
            canvas.height = OUTPUT_SIZE;
            const ctx = canvas.getContext('2d');

            if (!ctx || !img) {
                return;
            }

            const outputScale = OUTPUT_SIZE / CROP_SIZE;
            const scale = imgMetaRef.current.baseScale * zoom * outputScale;

            ctx.save();
            ctx.translate(OUTPUT_SIZE / 2, OUTPUT_SIZE / 2);
            ctx.translate(position.x * outputScale, position.y * outputScale);
            ctx.scale(scale, scale);
            ctx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2);
            ctx.restore();

            canvas.toBlob(
                (blob) => {
                    if (!blob) {
                        return;
                    }

                    const file = new File([blob], 'avatar.jpg', {
                        type: 'image/jpeg',
                    });

                    // A File can't be assigned to input.files directly —
                    // DataTransfer is the standard workaround, and it's
                    // what lets this ride along in the surrounding
                    // <Form>'s normal multipart submission.
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    if (croppedFileInputRef.current) {
                        croppedFileInputRef.current.files = dataTransfer.files;
                    }

                    setPreviewUrl((prev) => {
                        if (prev?.startsWith('blob')) {
                            URL.revokeObjectURL(prev);
                        }

                        return URL.createObjectURL(blob);
                    });

                    if (imageSrc) {
                        URL.revokeObjectURL(imageSrc);
                    }
                    setImageSrc(null);
                    setIsCropping(false);
                },
                'image/jpeg',
                0.92,
            );
        }

        function onDrop(e: React.DragEvent<HTMLButtonElement>) {
            e.preventDefault();

            const file = e.dataTransfer.files?.[0];
            if (file) {
                handleFileSelected(file);
            }
        }

        return (
            <div className="flex shrink-0 flex-col items-center gap-3">
                {/* Selection-only input — never submitted directly. */}
                <input
                    ref={rawFileInputRef}
                    type="file"
                    accept={ACCEPTED_FILETYPES.join(',')}
                    className="hidden"
                    onChange={(e) => {
                        const file = e.target.files?.[0];
                        if (file) {
                            handleFileSelected(file);
                        }
                        e.target.value = '';
                    }}
                />

                {/* The field actually submitted with the surrounding form. */}
                <input
                    ref={croppedFileInputRef}
                    type="file"
                    name={name}
                    className="hidden"
                />

                <div
                    className="relative touch-none overflow-hidden rounded-full border bg-muted transition-[width,height] duration-200 motion-reduce:transition-none"
                    style={{ width: CROP_SIZE, height: CROP_SIZE }}
                    onPointerDown={isCropping ? onPointerDown : undefined}
                    onPointerMove={isCropping ? onPointerMove : undefined}
                    onPointerUp={isCropping ? onPointerUp : undefined}
                    onPointerLeave={isCropping ? onPointerUp : undefined}
                    onWheel={isCropping ? onWheel : undefined}
                >
                    {isCropping ? (
                        <img
                            ref={imgRef}
                            src={imageSrc ?? undefined}
                            onLoad={onImageLoad}
                            alt=""
                            draggable={false}
                            className="absolute top-1/2 left-1/2 max-w-none touch-none select-none"
                            style={{
                                transform: `translate(-50%, -50%) translate(${position.x}px, ${position.y}px) scale(${imgMetaRef.current.baseScale * zoom})`,
                            }}
                        />
                    ) : (
                        <button
                            type="button"
                            onClick={() => rawFileInputRef.current?.click()}
                            onDrop={onDrop}
                            onDragOver={(e) => e.preventDefault()}
                            aria-label={
                                previewUrl
                                    ? __('components/avatar-uploader.change')
                                    : __('components/avatar-uploader.upload')
                            }
                            className="size-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            {previewUrl ? (
                                <img
                                    src={previewUrl}
                                    alt=""
                                    className="size-full object-cover"
                                />
                            ) : (
                                <span className="flex size-full items-center justify-center text-muted-foreground">
                                    <ImagePlus
                                        className="size-8"
                                        aria-hidden="true"
                                    />
                                </span>
                            )}
                        </button>
                    )}
                </div>

                {isCropping ? (
                    <>
                        <div className="flex w-full max-w-56 items-center gap-2">
                            <ZoomOut
                                className="size-4 text-muted-foreground"
                                aria-hidden="true"
                            />
                            <input
                                type="range"
                                min={MIN_ZOOM}
                                max={MAX_ZOOM}
                                step={0.01}
                                value={zoom}
                                onChange={(e) => onZoomChange(Number(e.target.value))}
                                aria-label={__(
                                    'components/avatar-uploader.zoom_label',
                                )}
                                className="w-full"
                            />
                            <ZoomIn
                                className="size-4 text-muted-foreground"
                                aria-hidden="true"
                            />
                        </div>

                        <div className="flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={cancelCrop}
                            >
                                {__('components/avatar-uploader.cancel')}
                            </Button>
                            <Button type="button" size="sm" onClick={confirmCrop}>
                                {__('components/avatar-uploader.confirm')}
                            </Button>
                        </div>
                    </>
                ) : (
                    <p className="max-w-56 text-center text-xs text-muted-foreground">
                        {__('components/avatar-uploader.hint')}
                    </p>
                )}

                <InputError message={clientError ?? error} />
            </div>
        );
    },
);

export default AvatarUploader;
