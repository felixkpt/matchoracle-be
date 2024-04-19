import MatchesPageHeader from '@/components/Matches/MatchesPageHeader';
import { CompetitionInterface, SeasonsListInterface } from '@/interfaces/FootballInterface';

interface Props extends SeasonsListInterface {
    actionTitle?: string
    actionButton?: string
    record: CompetitionInterface | undefined;
    fromToDates?: Array<Date | string | undefined>;
    setFromToDates?: React.Dispatch<React.SetStateAction<Array<Date | string | undefined>>>;
    setUseDates?: React.Dispatch<React.SetStateAction<boolean>>;
    setKey?: React.Dispatch<React.SetStateAction<number>>;
    hideSeasons?: boolean
}

const CompetitionSubHeader = ({ actionTitle, actionButton, fromToDates, setFromToDates, setUseDates, setKey }: Props) => {

    function handleSetStartDate(fromToDates: [string, string]) {
        if (setFromToDates) {
            setFromToDates(fromToDates)
        }
        if (fromToDates && fromToDates.length == 2 && fromToDates[1]) {
            if (setUseDates) {
                setUseDates(true)
            }
            if (setKey) {
                setKey((curr: number) => curr += 1)
            }
        }
    }

    return (
        <div className='header-title p-2 row justify-content-between'>

            <div className='row align-items-center justify-content-between position-relative'>
                <div className='col-12'>
                    <div className="w-100 d-flex align-items-center justify-content-xl-end gap-2">
                        <div className="col">
                            {
                                typeof setFromToDates === 'function'
                                &&
                                <MatchesPageHeader fromToDates={fromToDates} setFromToDates={handleSetStartDate} />
                            }
                        </div>
                        {
                            actionButton
                            &&
                            <button type="button" className="btn btn-primary" id="fetchStandingsButton" data-bs-toggle="modal" data-bs-target={`#${actionButton}`}>{actionTitle || 'Action'}</button>
                        }
                    </div>
                </div>
            </div>

        </div>
    )
}

export default CompetitionSubHeader