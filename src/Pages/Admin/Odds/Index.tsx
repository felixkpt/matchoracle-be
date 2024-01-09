import Error404 from '@/Pages/ErrorPages/Error404';
import AutoTable from '@/components/AutoTable';
import useListSources from '@/hooks/apis/useListSources';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import { useEffect, useState } from 'react';
import "react-datepicker/dist/react-datepicker.css";
import FormatDate from '@/utils/FormatDate';
import { useLocation, useNavigate } from 'react-router-dom';
import MatchesPageHeader from '../Matches/Includes/MatchesPageHeader';
import { oddsColumns } from '@/utils/constants';

const Index = () => {

    const { competitions: list_sources } = useListSources()

    const location = useLocation();
    const navigate = useNavigate();

    const errorsState = useRouteParamValidation();

    const [baseUri, setBaseUri] = useState(`/admin/odds/`)
    const [previousUrl, setPreviousUrl] = useState<string | null>(null)

    useEffect(() => {

        let url = location.pathname
        setBaseUri(url ? `${url}` : `/admin/odds/`)

        if (previousUrl !== location.pathname) {
            setPreviousUrl(location.pathname)
        }

    }, [location.pathname]);

    const initialDates: Array<string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<string | undefined>>(initialDates);

    useEffect(() => {
        let combinedDates = '';
        if (fromToDates[0]) {
            combinedDates = fromToDates[0];
            if (fromToDates[1]) {
                if (combinedDates != fromToDates[1])
                    combinedDates = `${combinedDates}/to/${fromToDates[1]}`;

                const newUrl = `/admin/odds/${combinedDates}`;
                navigate(newUrl);
            }
        }
    }, [fromToDates, history])
   
    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <MatchesPageHeader title={'Odds List'} fromToDates={fromToDates} setFromToDates={setFromToDates} />
                        <AutoTable
                            key={baseUri}
                            baseUri={baseUri}
                            columns={oddsColumns}
                            search={true}
                            list_sources={list_sources}
                            perPage={200}
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

