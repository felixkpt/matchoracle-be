import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  return (
    <div>
      <h3>GameScoreStatus List</h3>
      <div>
        <div className='d-flex justify-content-end'>
          <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#GameScoreStatus">Create status</button>
        </div>
        <AutoTable
          baseUri='/admin/settings/picklists/statuses/game-score-statuses'
          columns={[
            {
              label: 'ID',
              key: 'id',
            },
            {
              label: 'Status Name',
              key: 'name',
            },
            {
              label: 'Slug',
              key: 'slug',
            },
            {
              label: 'Icon',
              key: 'Icon',
            },
            {
              label: 'Class',
              key: 'class',
            },
            { key: 'Created_by' },
            {
              label: 'Created At',
              key: 'Created_at',
            },
            {
              label: 'Action',
              key: 'action',
            },
          ]}
          getModelDetails={setModelDetails}
          search={true}
          tableId='GameScoreStatusTable'
        />
      </div>
      {
        modelDetails && <><AutoModal id={`GameScoreStatus`} modelDetails={modelDetails} actionUrl='/admin/settings/picklists/statuses/game-score-statuses' /></>
      }
    </div>
  );
};

export default Index;