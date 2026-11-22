import { Head, useForm } from '@inertiajs/react';
import { Card, CardBody } from '@/components/ui';
import { useEffect, useState } from 'react';
import AppLayout from '@/layouts/AppLayout';

interface Error {
    id: string;
    timestamp: string;
    type: 'error' | 'warning' | 'info';
    message: string;
    component: string;
    url?: string;
    resolved: boolean;
}

interface Props {
    errorTypes?: string[];
    resolvedOnly?: boolean;
}

export default function ErrorDashboard(props: Props) {
    const [errors, setErrors] = useState<Error[]>([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState<'all' | 'errors' | 'warnings' | 'info'>('all');
    const [resolvedOnly, setResolvedOnly] = useState(false);

    useEffect(() => {
        fetch('/admin/errors', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                setErrors(data.errors || []);
                setLoading(false);
            })
            .catch(err => {
                console.error('Failed to fetch errors:', err);
                setLoading(false);
            });
    }, [filter, resolvedOnly]);

    const filteredErrors = errors.filter((error: Error) => {
        if (resolvedOnly && !error.resolved) return false;
        if (!resolvedOnly && error.resolved) return true;
        if (filter === 'errors' && error.type !== 'error') return false;
        if (filter === 'warnings' && error.type !== 'warning') return false;
        if (filter === 'info' && error.type !== 'info') return false;
        return true;
    });

    return (
        <div className="p-8">
            <h1 className="text-2xl font-bold mb-4">Error Dashboard</h1>

            <div className="flex gap-2 mb-4">
                <button onClick={() => setFilter('all')} className="rounded-md border border-btn">
                    All
                </button>
                <button onClick={() => setFilter('errors')} className="rounded-md border border-btn">
                    Errors
                </button>
                <button onClick={() => setFilter('warnings')} className="rounded-md border border-btn">
                    Warnings
                </button>
                <button onClick={() => setFilter('info')} className="rounded-md border border-btn">
                    Info
                </button>
            </div>

            <p className="text-text-muted">Error tracking dashboard</p>

            {loading ? (
                <div className="pt-8">
                    <div className="animate-spin h-8 w-8 border-b-2 border-current mx-auto my-4" />
                    <p>Loading errors...</p>
                </div>
            ) : filteredErrors.length === 0 ? (
                <div className="pt-8 text-center text-text-muted">
                    <p>No errors found.</p>
                    <p className="mt-2 text-sm">Filters applied: {filter !== 'all' ? filter : 'none'} {resolvedOnly ? '(resolved only)' : ''}</p>
                </div>
            ) : (
                <Card>
                    <CardBody className="p-0">
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableCell className="w-24">Date</TableCell>
                                    <TableCell>Type</TableCell>
                                    <TableCell>Component</TableCell>
                                    <TableCell className="w-40">Message</TableCell>
                                    <TableCell>Resolved</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {filteredErrors.map((error: Error) => (
                                    <TableRow key={error.id} className="border-b">
                                        <TableCell className="font-medium text-sm">
                                            {new Date(error.timestamp).toLocaleString()}
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                className={`inline-flex items-center gap-1 px-2 rounded text-xs ${{
                                                    error: 'bg-red-100 text-red-600',
                                                    warning: 'bg-yellow-100 text-yellow-600',
                                                    info: 'bg-blue-100 text-blue-600',
                                                }[error.type]}`}
                                            >
                                                {error.type[0].toUpperCase() + error.type.slice(1)}
                                            </span>
                                        </TableCell>
                                        <TableCell>{error.component}</TableCell>
                                        <TableCell className="truncate max-w-xs">{error.message}</TableCell>
                                        <TableCell>
                                            <span
                                                className={`px-2 py-1 rounded text-xs ${error.resolved ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'}`}
                                            >
                                                {error.resolved ? 'Yes' : 'No'}
                                            </span>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardBody>
                </Card>
            )}

            <div className="mt-4 flex justify-between pt-4 border-t">
                <span className="text-sm text-text-muted">
                    {errors.length} total errors{' '}
                    {filteredErrors.length > 0 ? (
                        <span className="ml-1 text-xs font-bold">
                            {filteredErrors.length} showing
                        </span>
                    ) : ''}
                </span>
                <button onClick={() => window.location.reload()} className="text-xs font-medium text-primary hover:underline">
                    Refresh
                </button>
            </div>
        </div>
    );
}