import Index from '../../../Categories/Index'

type Props = {
    category: any
}

const Categories = (props: Props) => {
  return (
    <div>
        <Index category={props.category} />
    </div>
  )
}

export default Categories