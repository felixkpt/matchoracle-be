import Loader from "./Loader"

type Props = {
    content?: string
}

const SubmitButton = ({ content }: Props) => {
    return (
        <button className="btn btn-sm btn-primary d-flex align-items-center gap-1" type='submit'><div className="submit-btn-loader d-none"><Loader message='' /></div>{content || 'Submit'}</button>)
}

export default SubmitButton