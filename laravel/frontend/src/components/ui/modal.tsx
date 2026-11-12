import { useEffect, useRef, type ReactNode } from 'react';

interface ModalProps {
    open: boolean;
    onClose: () => void;
    title?: string;
    children: ReactNode;
    maxWidth?: string;
}

export function Modal({ open, onClose, title, children, maxWidth = 'max-w-lg' }: ModalProps) {
    const dialogRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = previousOverflow;
        };
    }, [onClose, open]);

    useEffect(() => {
        if (open && dialogRef.current) {
            dialogRef.current.focus();
        }
    }, [open]);

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/55 p-4 backdrop-blur-sm">
            <button
                type="button"
                aria-label="Close modal"
                className="absolute inset-0 cursor-default"
                onClick={onClose}
            />
            <div
                ref={dialogRef}
                tabIndex={-1}
                role="dialog"
                aria-modal="true"
                aria-labelledby={title ? 'modal-title' : undefined}
                className={`relative w-full ${maxWidth} rounded-[var(--radius-card)] border border-border bg-surface-raised p-0 shadow-modal outline-none`}
            >
                <div className="flex items-center justify-between border-b border-border px-5 py-4">
                    {title && <h2 id="modal-title" className="text-lg font-semibold text-text">{title}</h2>}
                    <button
                        type="button"
                        onClick={onClose}
                        className="inline-flex h-9 w-9 items-center justify-center rounded-full text-text-muted hover:bg-surface hover:text-text"
                        aria-label="Close"
                    >
                        <i className="fas fa-xmark" aria-hidden="true" />
                    </button>
                </div>
                <div className="max-h-[80vh] overflow-y-auto p-5">{children}</div>
            </div>
        </div>
    );
}
