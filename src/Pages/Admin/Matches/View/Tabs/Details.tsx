import { GetItem, ResponseData } from '@/interfaces'

type Props = {
    game: GetItem
}

import SimpleTable from '@/components/SimpleTable';
import AutoModal from '@/components/Modals/AutoModal';
import { useState } from 'react';
import { Dropdown } from 'flowbite-react';

const Details = ({ game }: Props) => {

    const baseUri = 'games/game/' + game.data.year + '/' + game.data.id;

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
                    model_name={game.model_name}
                    fillable={game.fillable}
                    baseUri={baseUri}
                    row={game.data}
                    size='lg'
                    action={'edit'}
                    response={response}
                />
            )}
            <SimpleTable row={game.data} />
        </div>
    );
};

export default Details;
