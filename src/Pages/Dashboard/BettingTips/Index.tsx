import Select from 'react-select';
import Error404 from '@/Pages/ErrorPages/Error404';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
import AutoTabs from '@/components/Autos/AutoTabs';
import { TabInterface } from '@/interfaces/UncategorizedInterfaces';
import GGTips from './Tabs/GGTips';
import NGTips from './Tabs/NGTips';
import Over25Tips from './Tabs/Over25Tips';
import HomeWinTips from './Tabs/HomeWinTips';
import DrawTips from './Tabs/DrawTips';
import AwayWinTips from './Tabs/AwayWinTips';
import Under25Tips from './Tabs/Under25Tips';
import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';
import useFromToDates from '@/hooks/useFromToDates';
import { useEffect, useState } from 'react';
import { predictionModes, bettingStrategies } from '@/utils/constants';
import { BettingStrategyInterface, PredictionModeInterface } from '@/interfaces/FootballInterface';
import PredictionsModeSwitcher from '@/components/Predictions/PredictionsModeSwitcher';
import AllTips from './Tabs/AllTips';

const Index = () => {

    const errorsState = useRouteParamValidation();
    const { fromToDates, setFromToDates, baseUri, previousUrl } = useFromToDates('/dashboard/betting-tips/');

    const [predictionMode, setPredictionMode] = useState<PredictionModeInterface | null>();

    const [bettingStrategy, setBettingStrategy] = useState<BettingStrategyInterface | null>();

    useEffect(() => {
        if (!predictionMode && predictionModes) {
            setPredictionMode(predictionModes[0])
        }
        if (!bettingStrategy && bettingStrategies) {
            setBettingStrategy(bettingStrategies[0])
        }

    }, [predictionModes, bettingStrategies])


    const [uri, setUri] = useState<string>('')
    useEffect(() => {

        if (predictionMode && bettingStrategy) {
            setUri(`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}&betting_strategy_id=${bettingStrategy ? bettingStrategy.id : 0}`)
        }

    }, [baseUri, predictionMode, bettingStrategy])


    const tabs: TabInterface[] = [
        {
            name: 'All tips',
            content: <AllTips uri={uri} />

        },
        {
            name: 'Home win tips',
            content: <HomeWinTips uri={uri} />

        },
        {
            name: 'Draw tips',
            content: <DrawTips uri={uri} />

        },
        {
            name: 'Away win tips',
            content: <AwayWinTips uri={uri} />

        },
        {
            name: 'Over 25 tips',
            content: <Over25Tips uri={uri} />

        },
        {
            name: 'Under 25 tips',
            content: <Under25Tips uri={uri} />

        },
        {
            label: 'BTS - Yes tips',
            name: 'gg tips',
            content: <GGTips uri={uri} />

        },
        {
            label: 'BTS - No tips',
            name: 'ng tips',
            content: <NGTips uri={uri} />
        },
    ]

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <div className="row shadow-sm">
                            <div className="col-xl-6">
                                <MatchesPageHeader title={'Betting Tips List'} fromToDates={fromToDates} setFromToDates={setFromToDates} className="shadow-none" />
                            </div>
                            <div className="col-xl-3">
                                <PredictionsModeSwitcher predictionMode={predictionMode} predictionModes={predictionModes} setPredictionMode={setPredictionMode} />
                            </div>
                            <div className="col-xl-3">
                                <div className='d-flex gap-1 align-items-center justify-content-center shadow-sm px-2 rounded'>
                                    <div className='text-nowrap'>{'Strategy'}:</div>
                                    <Select
                                        className="tips-mode-input form-control border-0"
                                        classNamePrefix="select"
                                        defaultValue={predictionMode || null}
                                        isDisabled={false}
                                        isLoading={false}
                                        isClearable={false}
                                        isSearchable={false}
                                        placeholder="Select strategy"
                                        name='strategy_id'
                                        options={bettingStrategies || []}
                                        onChange={(v: any) => setBettingStrategy(v)}
                                        getOptionValue={(option: any) => `${option['id']}`}
                                        getOptionLabel={(option: any) => option['name']}
                                    />
                                </div>
                            </div>
                        </div>
                        <div className='mt-4' key={uri}>
                            {
                                uri != '' &&
                                <AutoTabs key={uri} tabs={tabs} />
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

