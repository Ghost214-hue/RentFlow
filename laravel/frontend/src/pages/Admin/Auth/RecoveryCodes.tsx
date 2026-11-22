import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    codes: string[];
}

const pretty = (c: string) => `${c.slice(0, 5)}-${c.slice(5)}`;

/** Shown once after 2FA enrolment or regeneration. Each code works once. */
export default function RecoveryCodes({ codes }: Props) {
    const [saved, setSaved] = useState(false);
    const [copied, setCopied] = useState(false);
    const text = codes.map(pretty).join('\n');

    async function copy() {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(true);
        } catch {
            setCopied(false);
        }
    }

    function download() {
        const url = URL.createObjectURL(new Blob([text + '\n'], { type: 'text/plain' }));
        const a = document.createElement('a');
        a.href = url;
        a.download = 'rentflow-admin-recovery-codes.txt';
        a.click();
        URL.revokeObjectURL(url);
    }

    return (
        <>
            <Head title="Save your recovery codes" />
            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4">
                <div className="w-full max-w-md rounded-3xl bg-slate-900/90 p-8 shadow-2xl border border-slate-800 text-white">
                    <h1 className="text-xl font-bold">Save your recovery codes</h1>
                    <p className="text-sm text-slate-300 mt-2 leading-relaxed">
                        If you lose your phone, these codes are the only way back in. Each works once. They will not be shown again.
                    </p>

                    <ul className="mt-5 grid grid-cols-2 gap-2 font-mono text-sm">
                        {codes.map((c) => (
                            <li key={c} className="rounded-lg border border-slate-800 bg-slate-950/80 px-3 py-2 text-center text-indigo-300 select-all">
                                {pretty(c)}
                            </li>
                        ))}
                    </ul>

                    <div className="mt-4 flex gap-2">
                        <button type="button" onClick={copy} className="flex-1 rounded-xl border border-slate-700 px-3 py-2 text-sm hover:bg-slate-800">
                            {copied ? 'Copied' : 'Copy'}
                        </button>
                        <button type="button" onClick={download} className="flex-1 rounded-xl border border-slate-700 px-3 py-2 text-sm hover:bg-slate-800">
                            Download
                        </button>
                    </div>

                    <label className="mt-5 flex items-start gap-2 text-sm text-slate-300">
                        <input type="checkbox" checked={saved} onChange={(e) => setSaved(e.target.checked)} className="mt-1" />
                        I have stored these codes somewhere safe (password manager or printed).
                    </label>

                    <button
                        type="button"
                        disabled={!saved}
                        onClick={() => router.post('/admin/2fa/codes/ack')}
                        className="mt-4 w-full rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3 text-sm font-semibold disabled:opacity-40"
                    >
                        Continue to dashboard
                    </button>
                </div>
            </div>
        </>
    );
}
