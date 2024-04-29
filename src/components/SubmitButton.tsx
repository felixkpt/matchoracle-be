import { ReactNode } from 'react';
import Loader from "./Loader";

type Props = {
    children?: ReactNode; // Allow children as content
    content?: string; // Allow string content
    className?: string; // Additional className
    loading?: boolean; // Loading status
}

const SubmitButton = ({ children, content, className, loading }: Props) => {
    return (
        <button className={`btn btn-primary d-flex align-items-center gap-1 ${className ? className : ''} ${loading ? 'disabled' : ''}`} type='submit'>
            <div className={`submit-btn-loader ${loading ? '' : 'd-none'}`}><Loader message='' /></div>{children || content || 'Submit'}
        </button>)
}

export default SubmitButton