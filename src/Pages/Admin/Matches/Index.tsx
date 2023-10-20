import Error404 from '@/Pages/ErrorPages/Error404';
import AutoTable from '@/components/AutoTable';
import useListSources from '@/hooks/apis/useListSources';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import { useEffect, useState } from 'react';
import "react-datepicker/dist/react-datepicker.css";
import MatchesPageHeader from './Includes/MatchesPageHeader';
import FormatDate from '@/utils/FormatDate';

const Index = () => {

    const { competitions: list_sources } = useListSources()

    const errorsState = useRouteParamValidation();

    const columns = [
        { key: 'home_team.name' },
        { key: 'away_team.name' },
        { label: 'half_time', key: 'half_time' },
        { label: 'full_time', key: 'full_time' },
        { label: 'Status', key: 'Status' },
        { label: 'User', key: 'user_id' },
        { key: 'utc_date' },
        { label: 'Created At', key: 'Created_at' },
        { label: 'Action', key: 'action' },
    ]

    const [previousUrl, setPreviousUrl] = useState<string | null>(null)

    useEffect(() => {

        if (previousUrl !== location.pathname) {
            setPreviousUrl(location.pathname)
        }

    }, [location.pathname]);

    const [startDate, setStartDate] = useState(FormatDate.YYYYMMDD(new Date()));

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <MatchesPageHeader title={'Matches List'} startDate={startDate} setStartDate={setStartDate} />
                        <AutoTable
                            key={startDate}
                            baseUri={`/admin/matches/${startDate}`}
                            columns={columns}
                            search={true}
                            list_sources={list_sources}
                        />
                    </div>
                    :
                    <div>
                        {
                            errorsState === 2
                            &&
                            <Error404 previousUrl={previousUrl} currentUrl={location.pathname} />
                        }
                    </div>
            }
        </div>
    );
};

export default Index;

