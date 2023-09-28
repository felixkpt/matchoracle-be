import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';

type Props = {
  category?: any
}

const Index = ({ category }: Props) => {
  const [modelDetails, setModelDetails] = useState({})

  const list_sources = {
  };

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
            label: 'Category Title',
            key: 'title',
          },
          {
            label: 'Category Slug',
            key: 'slug',
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
        modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl={`/admin/posts/categories?parent_category_id=${category ? category.id : '0'}`} list_sources={list_sources} modalSize='modal-lg' /></>
      }
    </div>
  );
};

export default Index;

