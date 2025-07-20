import Error404 from '@/Pages/ErrorPages/Error404';
import AutoTable from '@/components/Autos/AutoTable';
import useListSources from '@/hooks/list-sources/useListSources';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';
import useFromToDates from '@/hooks/useFromToDates';
import { oddsColumns } from '@/components/TableColumns';

const Index = () => {

    const { competitions: listSources } = useListSources()

    const initBaseUri = '/dashboard/odds/'

    const errorsState = useRouteParamValidation();
    const { fromToDates, setFromToDates, baseUri, previousUrl } = useFromToDates(initBaseUri);

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <div className="row shadow-sm">
                            <div className="col-xl-12">
                                <MatchesPageHeader title={'Odds List'} fromToDates={fromToDates} setFromToDates={setFromToDates} className="shadow-none" />
                            </div>
                        </div>
                        <AutoTable
                            key={baseUri}
                            baseUri={baseUri || initBaseUri}
                            columns={oddsColumns}
                            search={true}
                            listSources={listSources}
                            perPage={200}
                            tableId='oddsTable'
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

