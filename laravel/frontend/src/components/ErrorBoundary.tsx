import React, { ComponentType, ReactNode, useState, useEffect } from 'react';

type ErrorBoundaryState = {
    hasError: boolean;
    error?: Error;
    errorInfo?: React.ErrorInfo;
};

type ErrorBoundaryProps = {
    fallback?: ReactNode;
    onError?: (error: Error) => void;
};

type ErrorBoundaryContextValue = {
    hasError: boolean;
    error?: Error;
    errorInfo?: React.ErrorInfo;
};

/**
 * React Error Boundary component that catches JavaScript errors
 * anywhere in the component tree below it, logs them, and displays
 * a fallback UI instead of the component tree that crashed.
 */
export class ErrorBoundary extends Component<
    ErrorBoundaryProps,
    ErrorBoundaryState
> {
    constructor(props: ErrorBoundaryProps) {
        super(props);
        this.state = {
            hasError: false,
            error: undefined,
            errorInfo: undefined,
        };
    }

    static getDerivedStateFromError(error: Error): ErrorBoundaryState {
        // Update state so the next render will show the fallback
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
        // Log the error to an error reporting service
        const { onError } = this.props;
        if (onError) {
            onError(error);
        }
        // You can also send the error to an error tracking service here
        console.error('Error caught by ErrorBoundary:', error, errorInfo);
    }

    render(): ReactNode {
        if (this.state.hasError) {
            // Render fallback UI
            const { fallback } = this.props;
            return fallback || (
                <div className="p-6 bg-red-100 text-red-800 rounded border border-red-400">
                    <h2 className="font-bold mb-2">Something went wrong</h2>
                    <p className="mb-4">We've encountered an error and are working to fix it.</p>
                    <button
                        onClick={() => window.location.reload()}
                        className="inline-block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                    >
                        Reload Page
                    </button>
                </div>
            );
        }
        return this.props.children;
    }
}

/**
 * Hook for using error state from an ErrorBoundary
 */
export function useErrorBoundary(): ErrorBoundaryState {
    const [state, setState] = useState<ErrorBoundaryState>({
        hasError: false,
        error: undefined,
        errorInfo: undefined,
    });

    return state;
}

/**
 * Higher-order component that wraps a component with an ErrorBoundary
 */
export function withErrorBoundary<P>(
    Component: ComponentType<P>,
    fallback?: ReactNode
): ComponentType<P & ErrorBoundaryProps> {
    return (props: P & ErrorBoundaryProps) => {
        const Fallback = fallback || (
            <div className="p-6 bg-red-100 text-red-800 rounded border border-red-400">
                <h2 className="font-bold mb-2">Something went wrong</h2>
                <p className="mb-4">We've encountered an error.</p>
                <button
                    onClick={() => window.location.reload()}
                    className="inline-block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                >
                    Reload Page
                </button>
            </div>
        );

        return <ErrorBoundary fallback={Fallback} {...props} />;
    };
}