import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  return (
    <div>
      <h3>Venues List</h3>
      <div>
        <div className='d-flex justify-content-end'>
          <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Venues">Create venue</button>
        </div>
        <AutoTable
          baseUri='/dashboard/teams/venues'
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
        />
      </div>
      {
        modelDetails && <><AutoModal id={`Venues`} modelDetails={modelDetails} actionUrl='/dashboard/teams/venues' /></>
      }
    </div>
  );
};

export default Index;