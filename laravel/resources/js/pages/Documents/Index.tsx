import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { PropertyDocument, RenterOption } from '@/types';

interface Props {
    documents: PropertyDocument[];
    types: string[];
    filters: { type: string | null; property_id: number | null };
    canManage: boolean;
    properties: RenterOption[];
    flash?: { success?: string | null };
}

interface DocumentForm {
    title: string;
    content: string;
    type: string;
    version: string;
    is_active: boolean;
    property_id: string;
}

const EMPTY: DocumentForm = {
    title: '',
    content: '',
    type: 'rules',
    version: '1.0',
    is_active: true,
    property_id: '',
};

const field = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

const TYPE_TONE: Record<string, string> = {
    rules: 'bg-blue-100 text-blue-700',
    regulations: 'bg-violet-100 text-violet-700',
    policy: 'bg-amber-100 text-amber-700',
    other: 'bg-slate-100 text-slate-600',
};

/**
 * Rules and documents.
 *
 * Ported from frontend/pages/documents.php, which the sidebar labels "Rules".
 * Renters see this read-only: these are the landlord's terms.
 */
export default function DocumentsIndex({
    documents,
    types,
    filters,
    canManage,
    properties,
    flash,
}: Props) {
    const [open, setOpen] = useState(false);
    const [editing, setEditing] = useState<PropertyDocument | null>(null);
    const [expanded, setExpanded] = useState<number | null>(null);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<DocumentForm>(EMPTY);

    function fill(values: DocumentForm) {
        clearErrors();
        setData(values);
    }

    function openNew() {
        fill(EMPTY);
        setEditing(null);
        setOpen(true);
    }

    function openEdit(doc: PropertyDocument) {
        fill({
            title: doc.title,
            content: doc.content,
            type: doc.type,
            version: doc.version ?? '1.0',
            is_active: doc.is_active,
            property_id: doc.property_id === null ? '' : String(doc.property_id),
        });
        setEditing(doc);
        setOpen(true);
    }

    function close() {
        setOpen(false);
        setEditing(null);
        fill(EMPTY);
    }

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => close() };
        if (editing) {
            put(`/documents/${editing.id}`, options);
            return;
        }
        post('/documents', options);
    }

    function remove(doc: PropertyDocument) {
        if (!window.confirm(`Delete "${doc.title}"?`)) return;
        router.delete(`/documents/${doc.id}`, { preserveScroll: true });
    }

    function filterByType(type: string) {
        router.get('/documents', { type: type || undefined }, { preserveState: true });
    }

    return (
        <>
            <Head title="Rules" />

            <AppLayout
                title="Rules"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Rules &amp; Documents</h1>
                            <p className="text-slate-500 mt-1">Terms published to your renters</p>
                        </div>

                        {canManage && (
                            <button
                                type="button"
                                onClick={openNew}
                                className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                            >
                                <i className="fas fa-plus" aria-hidden="true" />
                                New Document
                            </button>
                        )}
                    </>
                }
            >
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                <div className="mb-4 flex flex-wrap gap-2">
                    <button type="button" onClick={() => filterByType('')}
                        className={`px-3 py-1.5 rounded-full text-xs font-medium transition-colors ${
                            !filters.type ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'
                        }`}>
                        All
                    </button>
                    {types.map((t) => (
                        <button key={t} type="button" onClick={() => filterByType(t)}
                            className={`px-3 py-1.5 rounded-full text-xs font-medium capitalize transition-colors ${
                                filters.type === t ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'
                            }`}>
                            {t}
                        </button>
                    ))}
                </div>

                {documents.length === 0 ? (
                    <p className="py-12 text-center text-slate-400">No documents published yet.</p>
                ) : (
                    <div className="space-y-3">
                        {documents.map((doc) => (
                            <article key={doc.id} className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                                <div className="p-5">
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <h2 className="font-semibold text-slate-900">{doc.title}</h2>
                                            <p className="text-xs text-slate-500 mt-1">
                                                <span className={`px-2 py-0.5 rounded-full text-xs font-medium capitalize mr-2 ${TYPE_TONE[doc.type] ?? TYPE_TONE.other}`}>
                                                    {doc.type}
                                                </span>
                                                {doc.version && <span className="mr-2">v{doc.version}</span>}
                                                {doc.property_name ?? 'All properties'}
                                            </p>
                                        </div>

                                        <div className="flex items-center gap-2 shrink-0">
                                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                doc.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'
                                            }`}>
                                                {doc.is_active ? 'Active' : 'Draft'}
                                            </span>

                                            <button type="button"
                                                onClick={() => setExpanded(expanded === doc.id ? null : doc.id)}
                                                className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                                                aria-label={`${expanded === doc.id ? 'Hide' : 'Show'} ${doc.title}`}
                                                aria-expanded={expanded === doc.id}>
                                                <i className={`fas ${expanded === doc.id ? 'fa-chevron-up' : 'fa-chevron-down'}`} aria-hidden="true" />
                                            </button>

                                            {canManage && (
                                                <>
                                                    <button type="button" onClick={() => openEdit(doc)}
                                                        className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                                                        aria-label={`Edit ${doc.title}`}>
                                                        <i className="fas fa-pen" aria-hidden="true" />
                                                    </button>
                                                    <button type="button" onClick={() => remove(doc)}
                                                        className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                                        aria-label={`Delete ${doc.title}`}>
                                                        <i className="fas fa-trash" aria-hidden="true" />
                                                    </button>
                                                </>
                                            )}
                                        </div>
                                    </div>

                                    {expanded === doc.id && (
                                        <div className="mt-4 pt-4 border-t border-blue-50">
                                            {/* Prose comes from the landlord, so it is
                                                rendered as text and never as HTML. */}
                                            <p className="text-sm text-slate-700 whitespace-pre-wrap">{doc.content}</p>
                                        </div>
                                    )}
                                </div>
                            </article>
                        ))}
                    </div>
                )}

                {open && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
                        onClick={(e) => { if (e.target === e.currentTarget) close(); }}
                        role="dialog" aria-modal="true" aria-labelledby="doc-modal-title">
                        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6"
                            onClick={(e) => e.stopPropagation()}>
                            <div className="flex items-center justify-between mb-4">
                                <h3 id="doc-modal-title" className="text-lg font-bold text-slate-900">
                                    {editing ? `Edit ${editing.title}` : 'New Document'}
                                </h3>
                                <button type="button" onClick={close} className="text-slate-400 hover:text-slate-600" aria-label="Close">
                                    <i className="fas fa-times text-xl" aria-hidden="true" />
                                </button>
                            </div>

                            <form onSubmit={submit} className="space-y-4" noValidate>
                                <div>
                                    <label htmlFor="docTitle" className="block text-sm font-medium text-slate-700 mb-1">Title</label>
                                    <input id="docTitle" value={data.title} onChange={(e) => setData('title', e.target.value)} className={field} required />
                                    {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                                </div>

                                <div>
                                    <label htmlFor="docContent" className="block text-sm font-medium text-slate-700 mb-1">Content</label>
                                    <textarea id="docContent" rows={8} value={data.content}
                                        onChange={(e) => setData('content', e.target.value)} className={field} required />
                                    {errors.content && <p className="mt-1 text-sm text-red-600">{errors.content}</p>}
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label htmlFor="docType" className="block text-sm font-medium text-slate-700 mb-1">Type</label>
                                        <select id="docType" value={data.type} onChange={(e) => setData('type', e.target.value)} className={field}>
                                            {types.map((t) => (
                                                <option key={t} value={t} className="capitalize">{t}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label htmlFor="docVersion" className="block text-sm font-medium text-slate-700 mb-1">Version</label>
                                        <input id="docVersion" value={data.version} onChange={(e) => setData('version', e.target.value)} className={field} />
                                    </div>
                                    <div>
                                        <label htmlFor="docProperty" className="block text-sm font-medium text-slate-700 mb-1">Property</label>
                                        <select id="docProperty" value={data.property_id} onChange={(e) => setData('property_id', e.target.value)} className={field}>
                                            <option value="">All properties</option>
                                            {properties.map((p) => (
                                                <option key={p.id} value={p.id}>{p.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <label className="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="w-4 h-4 rounded border-slate-300 text-blue-600" />
                                    <span className="text-sm text-slate-700">Visible to renters</span>
                                </label>

                                <div className="flex justify-end gap-3 pt-2">
                                    <button type="button" onClick={close}
                                        className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                                        Cancel
                                    </button>
                                    <button type="submit" disabled={processing}
                                        className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                                        {processing ? 'Saving...' : editing ? 'Save Changes' : 'Publish'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </AppLayout>
        </>
    );
}