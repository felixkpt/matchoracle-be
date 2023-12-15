import MatchesPageHeader from '@/Pages/Admin/Predictions/Includes/MatchesPageHeader';
import useAxios from '@/hooks/useAxios';
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
    setLocalKey?: any
    hideSeasons?: boolean
}

const CompetitionHeader = ({ title, actionTitle, actionButton, record, seasons, selectedSeason, setSelectedSeason, fromToDates, setFromToDates, setUseDates, setLocalKey, hideSeasons }: Props) => {

    const competition = record

    const { get, loading } = useAxios()

    const loadOptions = async (q: string) => {

        if (competition) {

            const currentValue = selectedSeason;

            const { data: fetchedOptions } = await get(`/admin/seasons?all=1&competition_id=${competition.id}&q=${q}`);

            if (currentValue) {

                const item = fetchedOptions.find((itm: any) => itm.id === currentValue.id)

                setSelectedSeason(item)
            }
            // Include the existing record's option in fetchedOptions if not already present
            if (currentValue && !fetchedOptions.some((option: any) => option.id === currentValue.id)) {
                fetchedOptions.push(currentValue);
            }

            return fetchedOptions
        }

    }

    function handleSetStartDate(fromToDates: any) {

        if (fromToDates && fromToDates.length == 2 && fromToDates[1]) {
            setFromToDates(fromToDates)
            setUseDates(true)
            setSelectedSeason(null)
            setLocalKey((curr: number) => curr += 1)
        }
    }

    function handleSetSelectedSeason(e: any) {
        setSelectedSeason(e)
        if (typeof setFromToDates === 'function') {
            setUseDates(false)
            setFromToDates([])
        }
        if (typeof setLocalKey === 'function') {
            setLocalKey((curr: number) => curr += 1)
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
                    <div>
                        {
                            competition && !hideSeasons
                            &&
                            <Select
                                className="form-control"
                                classNamePrefix="select"
                                defaultValue={selectedSeason}
                                isDisabled={false}
                                isLoading={false}
                                isClearable={false}
                                isSearchable={true}
                                placeholder="Select season"
                                name='season_id'
                                options={seasons}
                                onChange={(v) => handleSetSelectedSeason(v)}
                                getOptionValue={(option: any) => `${option['id']}`}
                                getOptionLabel={(option: any) => `${Str.before(option['start_date'], '-')} / ${Str.before(option['end_date'], '-')}`}
                            />
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
    )
}

export default CompetitionHeader