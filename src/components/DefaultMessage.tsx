type Props = {
    message?: string
    justify?: 'start' | 'center' | 'end'
}

const DefaultMessage = (props: Props) => {
    return (
        <div className="position-relative">
            <div className="position-absolute top-50 start-50 translate-middle w-100 p-1">
                <div className={`d-flex align-items-center justify-content-${props.justify || 'center'} gap-3`}>
                    {props.message || 'There\'s nothing here'}
                </div>
            </div>
        </div>
    )
}

export default DefaultMessage