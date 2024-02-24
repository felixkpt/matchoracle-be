import Error404 from '@/Pages/ErrorPages/Error404';
import useRouteParamValidation from '@/hooks/useRouteParamValidation';
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
import useFromToDates from '@/hooks/useFromToDates';
import { useEffect, useState } from 'react';
import Select from 'react-select';
import { predictionModes } from '@/utils/constants';

export interface PredictionModeInterface {
    id: string | number
    name: string
}

const Index = () => {

    const errorsState = useRouteParamValidation();
    const { fromToDates, setFromToDates, baseUri, previousUrl } = useFromToDates('/admin/betting-tips/');

    const [predictionMode, setPredictionMode] = useState<PredictionModeInterface | null>();

    useEffect(() => {
        if (predictionModes) {
            setPredictionMode(predictionModes[0])
        }
    }, [predictionModes])


    const changePredictionMode = (val: any) => {
        setPredictionMode(val);
    };

    const tabs: TabInterface[] = [
        {
            name: 'Home win tips',
            content: <HomeWinTips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />

        },
        {
            name: 'Draw tips',
            content: <DrawTips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />

        },
        {
            name: 'Away win tips',
            content: <AwayWinTips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />

        },
        {
            label: 'BTS - Yes tips',
            name: 'gg tips',
            content: <GGTips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />

        },
        {
            label: 'BTS - No tips',
            name: 'ng tips',
            content: <NGTips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />
        },
        {
            name: 'Over 25 tips',
            content: <Over25Tips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />

        },
        {
            name: 'Under 25 tips',
            content: <Under25Tips uri={`${baseUri}?prediction_mode_id=${predictionMode ? predictionMode.id : 0}`} />

        },
    ]

    return (
        <div>
            {
                errorsState === 0 ?
                    <div>
                        <div className="row shadow-sm">
                            <div className="col-md-9">
                                <MatchesPageHeader title={'Betting Tips List'} fromToDates={fromToDates} setFromToDates={setFromToDates} className="shadow-none" />
                            </div>
                            <div className="col-md-3">
                                <div className='d-flex gap-1 align-items-center shadow-sm px-2 rounded'>
                                    <div className='text-nowrap'>Tips Mode:</div> <Select
                                        className="form-control border-0"
                                        classNamePrefix="select"
                                        defaultValue={predictionMode || null}
                                        isDisabled={false}
                                        isLoading={false}
                                        isClearable={false}
                                        isSearchable={false}
                                        placeholder="Select tips mode"
                                        name='prediction_mode_id'
                                        options={predictionModes || []}
                                        onChange={(v: any) => changePredictionMode(v)}
                                        getOptionValue={(option: any) => `${option['id']}`}
                                        getOptionLabel={(option: any) => option['name']}
                                    />
                                </div>
                            </div>
                        </div>
                        <div className='mt-4' key={predictionMode ? predictionMode.id : 0}>
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

