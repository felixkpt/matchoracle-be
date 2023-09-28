import React from 'react'

type Props = {
    message: string
}

const Loader = (props: Props) => {
    return (
        <div className="position-absolute top-50 start-50 translate-middle w-100">
            <div className="d-flex align-items-center justify-content-center gap-3">
                <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                {props.message || 'Loading...'}
            </div>
        </div>
    )
}

export default Loader