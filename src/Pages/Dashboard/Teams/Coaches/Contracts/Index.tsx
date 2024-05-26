import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import useListSources from '@/hooks/list-sources/useListSources';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  const { competitions: listSources } = useListSources()

  return (
    <div>
      <h3>Contracts List</h3>
      <div>
        <div className='d-flex justify-content-end'>
          <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Contracts">Create contract</button>
        </div>
    
        <AutoTable
          baseUri='/dashboard/teams/coaches/contracts'
          columns={[
            {
              key: 'coach.name',
            },
            {
              key: 'team.name',
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
          listSources={listSources}
        />
      </div>
      {
        modelDetails && <><AutoModal id={`Contracts`} modelDetails={modelDetails} actionUrl='/dashboard/teams/coaches/contracts' listSources={listSources} /></>
      }
    </div>
  );
};

export default Index;