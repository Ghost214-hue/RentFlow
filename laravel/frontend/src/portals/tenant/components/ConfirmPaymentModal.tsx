/** Tenant Portal - Confirm Payment Modal
 * 
 * Allows tenant to confirm a payment against an outstanding bill.
 */
import { useAuth } from '@/shared/auth/context';
import { useState } from 'react';

export default function ConfirmPaymentModal() {
    const { user } = useAuth();
    const [amount, setAmount] = useState<string>('');
    const [selectedBill, setSelectedBill] = useState<{id: number; month: string; total: string} | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    if (!user) {
        return null;
    }

    return {
        // Modal would be rendered by parent component
        // This component provides the logic for confirming a payment
        confirmPayment: async (billId: number, amount: string) => {
            setLoading(true);
            setError(null);
            try {
                // Would call API to confirm payment
                // const res = await api.post(`/payments/${billId}/confirm`, { amount });
                setLoading(false);
            } catch (e) {
                setError('Failed to confirm payment. Please try again.');
                setLoading(false);
            }
        },
        
        reset: () => {
            setAmount('');
            setSelectedBill(null);
            setError(null);
        }
    };
}