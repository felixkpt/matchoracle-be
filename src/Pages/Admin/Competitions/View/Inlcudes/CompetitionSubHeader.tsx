import MatchesPageHeader from '@/Pages/Admin/Predictions/Includes/MatchesPageHeader';
import { CompetitionInterface, SeasonsListInterface } from '@/interfaces/FootballInterface';
import Str from '@/utils/Str'
import Select from 'react-select';

interface Props extends SeasonsListInterface {
    title: string
    actionTitle?: string
    actionButton?: string
    record: CompetitionInterface | undefined;
    fromToDates?: any
    setFromToDates?: any
    setUseDates?: any
    setKey?: any
    hideSeasons?: boolean
}

const CompetitionSubHeader = ({ title, actionTitle, actionButton, record, fromToDates, setFromToDates, setUseDates, setKey }: Props) => {

    function handleSetStartDate(fromToDates: any) {

        if (fromToDates && fromToDates.length == 2 && fromToDates[1]) {
            setFromToDates(fromToDates)
            setUseDates(true)
            setKey((curr: number) => curr += 1)
        }
    }

    return (
        <div className='header-title shadow-sm p-2 rounded mb-3 row justify-content-between'>

            <div className='row align-items-center justify-content-between position-relative'>
                <h3 className='col-12 col-xl-4 heading'>{title}</h3>
                <div className='col-12 col-xl-8 d-flex align-items-center justify-content-end gap-2'>
                    {
                        typeof setFromToDates === 'function'
                        &&
                        <MatchesPageHeader title={''} fromToDates={fromToDates} setFromToDates={handleSetStartDate} />
                    }
                    {
                        actionButton
                        &&
                        <button type="button" className="btn btn-primary" id="fetchStandingsButton" data-bs-toggle="modal" data-bs-target={`#${actionButton}`}>{actionTitle || 'Action'}</button>
                    }
                </div>
            </div>

        </div>
    )
}

export default CompetitionSubHeader