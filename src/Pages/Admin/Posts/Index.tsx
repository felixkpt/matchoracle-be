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
              label: 'Created At',
              key: 'Created_at',
            },{
              label: 'Created By',
              key: 'user.name',
            },
            {
              label: 'Status',
              key: 'Status',
              is_html: true,
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

