import Index from '../../Index'

type Props = {
  category: any
}

const Categories = (props: Props) => {

  const category = props.category;
  const listSelects = { parentCategoryId: category };

  return (
    <div>
      <Index category={props.category} params={'id=' + (props.category?.id || '0')} listSelects={listSelects} />
    </div>
  )
}

export default Categories