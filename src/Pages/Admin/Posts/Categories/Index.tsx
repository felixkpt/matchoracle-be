import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';

type Props = {
  category?: any
  params?: string
  list_selects?: any
}

const Index = ({ category, params, list_selects }: Props) => {
  const [modelDetails, setModelDetails] = useState({})

  const { posts: list_sources } = useListSources(params)

  return (
    <div>
      <PageHeader title={'Categories List'} action="button" actionText="Create Category" actionTargetId="AutoModal" permission='/admin/posts/categories' />
      <AutoTable
        baseUri={`/admin/posts/categories?parent_category_id=${category ? category.id : '0'}`}
        columns={[
          {
            label: 'ID',
            key: 'id',
          },
          {
            label: 'Category Name',
            key: 'name',
          },
          {
            label: 'Category Slug',
            key: 'slug',
          },
          {
            label: 'Created At',
            key: 'Created_at',
          },
          {
            label: 'Status',
            key: 'Status',
            is_html: true,
          },
          {
            label: 'Action',
            key: 'action',
          }

        ]}
        getModelDetails={setModelDetails}
        search={true}
        modalSize='modal-lg'
      />
      {
        modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl={`/admin/posts/categories?parent_category_id=${category ? category.id : '0'}`} list_sources={list_sources} list_selects={list_selects} modalSize='modal-lg' /></>
      }
    </div>
  );
};

export default Index;

