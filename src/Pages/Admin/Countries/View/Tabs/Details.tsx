import { GetItem, ResponseData } from '@/interfaces'

type Props = {
    country: GetItem
}

import SimpleTable from '@/components/SimpleTable';
import AutoModal from '@/components/Modals/AutoModal';
import { useState } from 'react';
import { Dropdown } from 'flowbite-react';

const Details = ({ country }: Props) => {

    const baseUri = 'countries/country/' + country.data.id;

    const [editOrCreateModalOpen, setCreateOrEditModalOpen] = useState(false);

    function response(response: ResponseData) {

        if (response.type == 'success') {
            if (response.type == 'success') {
                // setReload((prev: number) => prev + 1)

            }
        }

        setCreateOrEditModalOpen(false)
    }

    return (
        <div>
            <div className='flex justify-end w-full'>
                <Dropdown label="Actions">
                    <Dropdown.Item onClick={() => setCreateOrEditModalOpen(!editOrCreateModalOpen)}>
                        Edit
                    </Dropdown.Item>
                </Dropdown>

            </div>
            {editOrCreateModalOpen && (
                <AutoModal
                    model_name={'Country'}
                    fillable={country.fillable}
                    baseUri={baseUri}
                    row={country.data}
                    size='lg'
                    action={'edit'}
                    response={response}
                />
            )}
            <SimpleTable row={country.data} />
        </div>
    );
};

export default Details;
