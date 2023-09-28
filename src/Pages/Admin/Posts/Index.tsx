import AutoTable from '@/components/AutoTable';
import PageHeader from '@/components/PageHeader';

interface Props {
  category?: any
}

const Posts = ({ category }: Props) => {

    return (
    <div>
      <PageHeader title={'Posts List'} action="link" actionText="Create Doc" actionLink={`/admin/posts/create?category_id=${category ? category.id : '0'}`} permission='/admin/posts' />
      <div>
        <AutoTable
          baseUri={`/admin/posts?category_id=${category ? category.id : '0'}`}
          columns={[
            {
              label: 'ID',
              key: 'id',
            },
            {
              label: 'Title',
              key: 'title',
            },
            {
              label: 'slug',
              key: 'slug',
            }, {
              label: 'Content Short',
              key: 'content_short',
            },
            {
              label: 'Created At',
              key: 'created_at',
            },
            {
              label: 'Status',
              key: 'status',
            },
            {
              label: 'Action',
              key: 'action',
            },
          ]}
          search={true}
        />
      </div>
    </div>
  );
};

export default Posts;

