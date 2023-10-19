import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  return (
    <div>
      <h3>Addresses List</h3>
      <div>
        <div className='d-flex justify-content-end'>
          <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#Addresses">Create address</button>
        </div>
        <AutoTable
          baseUri='/admin/teams/addresses'
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
        modelDetails && <><AutoModal id={`Addresses`} modelDetails={modelDetails} actionUrl='/admin/teams/addresses' /></>
      }
    </div>
  );
};

export default Index;