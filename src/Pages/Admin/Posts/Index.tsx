import AutoTable from '@/components/AutoTable';
import PageHeader from '@/components/PageHeader';

interface Props {
  category?: any
}

const Posts = ({ category }: Props) => {

  return (
    <div>
      <PageHeader title={'Posts List'} action="link" actionText="Create Post" actionLink={`/admin/posts/create?category_id=${category ? category.id : '0'}`} permission='/admin/posts' />
      <div>
        <AutoTable
          baseUri={`/admin/posts?category_id=${category ? category.id : '0'}`}
          columns={[
            {
              label: 'Title',
              key: 'title',
            },
            {
              label: 'Author',
              key: 'user.name',
            },
            {
              label: 'Created At',
              key: 'Created_at',
            },
            {
              label: 'Status',
              key: 'Status',
            },
            {
              label: 'Action',
              key: 'action',
            },
          ]}
          search={true} 
          tableId='PostsTable'
        />
      </div>
    </div>
  );
};

export default Posts;

