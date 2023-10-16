import Index from '../../../Categories/Index'

type Props = {
  category: any
}

const Categories = (props: Props) => {

  const category = props.category;
  const list_selects = { parentCategoryId: category };

  return (
    <div>
      <Index category={props.category} params={'id=' + (props.category?.id || '0')} list_selects={list_selects} />
    </div>
  )
}

export default Categories