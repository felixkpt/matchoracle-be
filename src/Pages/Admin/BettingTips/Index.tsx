import Error404 from '@/Pages/ErrorPages/Error404';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import { useEffect, useState } from 'react';
import "react-datepicker/dist/react-datepicker.css";
import FormatDate from '@/utils/FormatDate';
import { useLocation, useNavigate } from 'react-router-dom';
import AutoTabs from '@/components/AutoTabs';
import { TabInterface } from '@/interfaces/UncategorizedInterfaces';
import GGTips from './Tabs/GGTips';
import NGTips from './Tabs/NGTips';
import Over25Tips from './Tabs/Over25Tips';
import HomeWinTips from './Tabs/HomeWinTips';
import DrawTips from './Tabs/DrawTips';
import AwayWinTips from './Tabs/AwayWinTips';
import Under25Tips from './Tabs/Under25Tips';
import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';

const Index = () => {

    const location = useLocation();
    const navigate = useNavigate();

    const errorsState = useRouteParamValidation();

    const [baseUri, setBaseUri] = useState(`/admin/betting-tips/`)
    const [previousUrl, setPreviousUrl] = useState<string | null>(null)

    useEffect(() => {

        let url = location.pathname
        setBaseUri(url ? `${url}` : `/admin/betting-tips/`)

        if (previousUrl !== location.pathname) {
            setPreviousUrl(location.pathname)
        }

    }, [location.pathname]);

    const initialDates: Array<string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
    const [fromToDates, setFromToDates] = useState<Array<string | undefined>>([]);

    useEffect(() => {
        let combinedDates = '';
        if (fromToDates[0]) {
            combinedDates = fromToDates[0];
            if (fromToDates[1]) {
                if (combinedDates != fromToDates[1])
                    combinedDates = `${combinedDates}/to/${fromToDates[1]}`;

                const newUrl = `/admin/betting-tips/${combinedDates}`;
                navigate(newUrl);
            }
        }
    }, [fromToDates, history])

    const tabs: TabInterface[] = [
        {
            name: 'Home win tips',
            content: <HomeWinTips uri={baseUri} />
        },
        {
            name: 'Draw tips',
            content: <DrawTips uri={baseUri} />
        },
        {
            name: 'Away win tips',
            content: <AwayWinTips uri={baseUri} />
        },
        {
            name: 'BTS - Yes tips',
            content: <GGTips uri={baseUri} />
        },
        {
            name: 'BTS - No tips',
            content: <NGTips uri={baseUri} />
        },
        {
            name: 'Over 25 tips',
            content: <Over25Tips uri={baseUri} />
        },
        {
            name: 'Under 25 tips',
            content: <Under25Tips uri={baseUri} />
        },
    ]

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <MatchesPageHeader title={'Betting Tips List'} fromToDates={fromToDates} setFromToDates={setFromToDates} />
                        <div className='mt-3'>
                            {
                                baseUri &&
                                <AutoTabs key={baseUri} tabs={tabs} />
                            }
                        </div>
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

