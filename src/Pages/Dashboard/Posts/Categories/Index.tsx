import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';

type Props = {
  category?: any
  params?: string
  listSelects?: any
}

const Index = ({ category, params, listSelects }: Props) => {
  const [modelDetails, setModelDetails] = useState({})

  const { posts: listSources } = useListSources(params)

  return (
    <div>
      <PageHeader title={'Categories List'} action="button" actionText="Create Category" actionTargetId="AutoModal" permission='/dashboard/posts/categories' />
      <AutoTable
        baseUri={`/dashboard/posts/categories?parent_category_id=${category ? category.id : '0'}`}
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
          { key: 'Created_by' },
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
        modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl={`/dashboard/posts/categories?parent_category_id=${category ? category.id : '0'}`} listSources={listSources} listSelects={listSelects} modalSize='modal-lg' /></>
      }
    </div>
  );
};

export default Index;

