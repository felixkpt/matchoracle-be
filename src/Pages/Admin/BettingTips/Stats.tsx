import AutoTable from '@/components/AutoTable';
import { useState } from 'react';

const Stats = () => {

    const [baseUri, setBaseUri] = useState('/admin/betting-tips/stats')

    const columns = [
        { key: 'type' },
        { key: 'Multiples' },
        { key: 'range' },
        { key: 'initial_bankroll' },
        { key: 'bankroll_topups' },
        { key: 'final_bankroll' },
        { key: 'total' },
        { key: 'won' },
        { key: 'won_percentage' },
        { key: 'gain' },
        { key: 'roi' },
        { key: 'longest_winning_streak' },
        { key: 'longest_losing_streak' },

        { label: 'Status', key: 'Status' },
        { key: 'Created_by' },
    ]

    return (
        <div>
            <div>
                <AutoTable
                    key={baseUri}
                    baseUri={baseUri}
                    columns={columns}
                    search={true}
                    perPage={200}
                />
            </div>
        </div>
    );
};

export default Stats;

