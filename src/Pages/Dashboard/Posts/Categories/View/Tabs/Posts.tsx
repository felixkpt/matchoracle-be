import PostsList from '../../../Index'

type Props = {
    category: any
}

const Posts = (props: Props) => {
    return (
        <div>
            <PostsList key={props.category?.id || 0} category={props.category} />
        </div>
    )
}

export default Posts