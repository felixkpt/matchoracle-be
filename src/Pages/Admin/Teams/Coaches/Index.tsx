import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import useListSources from '@/hooks/apis/useListSources';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  const { competitions: list_sources } = useListSources()

  return (
    <div>
      <h3>Coaches List</h3>
      <div>
        <div className='d-flex justify-content-end'>
          <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Coaches">Create coach</button>
        </div>
        <AutoTable
          baseUri='/admin/teams/coaches'
          columns={[
            {
              key: 'name',
            },
            {
              key: 'Created_at',
            },
            {
              key: 'Status',
            },
            {
              key: 'action',
            },
          ]}
          getModelDetails={setModelDetails}
          search={true}
          list_sources={list_sources}
          modalSize='modal-lg'
        />
      </div>
      {
        modelDetails && <><AutoModal id={`Coaches`} modelDetails={modelDetails} actionUrl='/admin/teams/coaches' list_sources={list_sources} modalSize='modal-lg' /></>
      }
    </div>
  );
};

export default Index;