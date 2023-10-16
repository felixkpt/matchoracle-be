type Props = {
    message?: string
    justify?: 'start' | 'center' | 'end'
}

const Loader = (props: Props) => {
    return (
        <div className="position-absolute top-50 start-50 translate-middle w-100 p-1">
            <div className={`d-flex align-items-center justify-content-${props.justify || 'center'} gap-3`}>
                <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                {props.message || 'Loading...'}
            </div>
        </div>
    )
}

export default Loader