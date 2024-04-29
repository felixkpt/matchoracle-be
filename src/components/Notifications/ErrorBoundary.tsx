import React, { Component, ErrorInfo, ReactNode } from 'react';

interface ErrorBoundaryProps {
  fallback?: string | ReactNode;
  children: ReactNode;
}

interface ErrorBoundaryState {
  hasError: boolean;
}

class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = {
      hasError: false,
    };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('Error caught in ErrorBoundary:', error);
    this.setState({ hasError: true });
  }

  componentDidUpdate(prevProps: ErrorBoundaryProps, prevState: ErrorBoundaryState) {
    if (prevState.hasError && this.state.hasError) {
      this.setState({ hasError: false });
    }
  }

  render() {
    const { fallback } = this.props;
    const defaultFallback = (
      <div>
        <h2>Oops, something went wrong!</h2>
        <p>An error occurred while loading this page. Please try again later.</p>
      </div>
    );

    return this.state.hasError ? (
      <div>{typeof fallback === 'string' ? fallback : fallback ? fallback : defaultFallback}</div>
    ) : (
      this.props.children
    );
  }
}

export default ErrorBoundary;
