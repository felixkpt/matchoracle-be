import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import useListSources from '@/hooks/apis/useListSources';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  const { competitions: list_sources } = useListSources()

  return (
    <div>
      <h3>Contracts List</h3>
      <div>
        <div className='d-flex justify-content-end'>
          <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Contracts">Create contract</button>
        </div>
    
        <AutoTable
          baseUri='/admin/teams/contracts'
          columns={[
            {
              key: 'name',
            },
            {
              key: 'team',
            },
            {
              key: 'start',
            },
            {
              key: 'until',
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
        />
      </div>
      {
        modelDetails && <><AutoModal id={`Contracts`} modelDetails={modelDetails} actionUrl='/admin/teams/contracts' list_sources={list_sources} /></>
      }
    </div>
  );
};

export default Index;