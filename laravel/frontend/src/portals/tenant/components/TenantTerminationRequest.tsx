/** Tenant Portal - Termination Request Component
 * 
 * Allows tenants to request to leave the property.
 */
import { useAuth } from '@/shared/auth/context';
import { useState } from 'react';

export default function TenantTerminationRequest() {
    const { user } = useAuth();
    const [status, setStatus] = useState<'active' | 'pending_termination' | 'terminated'>('active');
    const [effectiveDate, setEffectiveDate] = useState<string>(new Date().addMonths(1).format('Y-m-d'));
    const [reason, setReason] = useState<string>('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    if (!user) {
        return null;
    }

    useEffect(() => {
        // Would fetch tenancy status from API
        setLoading(false);
    }, [user?.id]);

    return {
        // Component logic for requesting tenancy termination
        status,
        setStatus,
        effectiveDate,
        setEffectiveDate,
        reason,
        setReason,
        loading,
        error,
        requestTermination: async () => {
            setLoading(true);
            setError(null);
            try {
                // Would call API to request termination
                // const res = await api.post('/terminations/request', { tenant_id: user.id, status, effective_date: effectiveDate, reason });
                setLoading(false);
            } catch (e) {
                setError('Failed to submit termination request. Please try again.');
                setLoading(false);
            }
        },
        reset: () => {
            setStatus('active');
            setEffectiveDate(new Date().addMonths(1).format('Y-m-d'));
            setReason('');
            setError(null);
        }
    };
}