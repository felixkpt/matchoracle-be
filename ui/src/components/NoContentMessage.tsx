import { useState, useEffect } from 'react';

type Props = {
    message?: string | undefined;
    justify?: 'start' | 'center' | 'end';
    fullpage?: boolean;
};

const NoContentMessage = (props: Props) => {
    const [showContent, setShowContent] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => setShowContent(true), 350);
        return () => clearTimeout(timer); // Clean up timer on unmount
    }, []);

    if (!showContent) return null;

    return (
        <div className="position-static">
            <div className={`p-1 ${props.fullpage ? 'position-absolute top-50 start-50 translate-middle w-100' : 'text-center'}`}>
                <div className={`d-flex align-items-center justify-content-${props.justify || 'center'} gap-3`}>
                    {props.message || "There's nothing here"}
                </div>
            </div>
        </div>
    );
};

export default NoContentMessage;
