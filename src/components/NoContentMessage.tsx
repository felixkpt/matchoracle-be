type Props = {
    message?: string
    justify?: 'start' | 'center' | 'end'
    fullpage?: boolean
}

const NoContentMessage = (props: Props) => {
    return (
        <div className="position-static">
            <div className={`p-1 ${props.fullpage ? 'position-absolute top-50 start-50 translate-middle w-100' : 'text-center'}`}>
                <div className={`d-flex align-items-center justify-content-${props.justify || 'center'} gap-3`}>
                    {props.message || 'There\'s nothing here'}
                </div>
            </div>
        </div>
    )
}

export default NoContentMessage