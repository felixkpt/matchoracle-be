import Error404 from '@/Pages/ErrorPages/Error404';
import AutoTable from '@/components/Autos/AutoTable';
import useListSources from '@/hooks/list-sources/useListSources';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';
import useFromToDates from '@/hooks/useFromToDates';
import { useEffect, useState } from 'react';
import { PredictionModeInterface } from '@/interfaces/FootballInterface';
import { predictionModes } from '@/utils/constants';

const Index = () => {

    const { competitions: listSources } = useListSources()

    const initialBaseUri = `/dashboard/matches/`
    const errorsState = useRouteParamValidation();
    const { fromToDates, setFromToDates, baseUri, previousUrl } = useFromToDates(initialBaseUri);

    const columns = [
        { key: 'id' },
        { key: 'Competition' },
        { key: 'home_team.name' },
        { key: 'away_team.name' },
        { label: 'half_time', key: 'half_time' },
        { label: 'full_time', key: 'full_time' },
        { key: 'utc_date' },
        { label: 'Status', key: 'Status' },
        { label: 'Created At', key: 'created_at' },
        { label: 'Action', key: 'action' },
    ]

    const [predictionMode, setPredictionMode] = useState<PredictionModeInterface | null>();

    useEffect(() => {
        if (predictionModes) {
            setPredictionMode(predictionModes[0])
        }
    }, [predictionModes])

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <div className="row shadow-sm">
                            <div className="col-xl-12">
                                <MatchesPageHeader title={'Matches List'} fromToDates={fromToDates} setFromToDates={setFromToDates} className="shadow-none" />
                            </div>
                        </div>
                        {
                            predictionMode &&
                            <div key={(predictionMode ? predictionMode.id : 0) + (baseUri || initialBaseUri)}>
                                <AutoTable
                                    baseUri={`${(baseUri || initialBaseUri)}?matches_mode_id=${predictionMode ? predictionMode.id : 0}`}
                                    columns={columns}
                                    search={true}
                                    listSources={listSources}
                                    perPage={100}
                                    tableId='matchesTable'
                                />
                            </div>

                        }
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

