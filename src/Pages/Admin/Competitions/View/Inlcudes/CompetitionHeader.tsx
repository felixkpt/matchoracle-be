import MatchesPageHeader from '@/Pages/Admin/Predictions/Includes/MatchesPageHeader';
import useAxios from '@/hooks/useAxios';
import { CompetitionInterface, CompetitionTabInterface } from '@/interfaces/FootballInterface';
import Str from '@/utils/Str'
import { SyntheticEvent, useState } from 'react';
import AsyncSelect from 'react-select/async';

interface Props extends CompetitionTabInterface {
    title: string
    actionTitle?: string
    actionButton?: string
    record: CompetitionInterface | undefined;
    startDate: any
    setStartDate: any
    setUseDate: any
    setLocalKey: any
}

const CompetitionHeader = ({ title, actionTitle, actionButton, record, selectedSeason, setSelectedSeason, startDate, setStartDate, setUseDate, setLocalKey }: Props) => {

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

    function handleSetStartDate(e: SyntheticEvent) {
        setStartDate(e)
        setUseDate(true)
        setLocalKey((curr: number) => curr += 1)
    }

    function handleSetSelectedSeason(e: any) {
        setSelectedSeason(e)
        setUseDate(false)
        setLocalKey((curr: number) => curr += 1)
    }

    return (
        <div className='header-title shadow-sm p-2 rounded mb-3 row justify-content-between'>

            <div className='row align-items-center justify-content-between position-relative'>
                <h3 className='col-12 col-xl-4 heading'>{title}</h3>
                <div className='col-12 col-xl-8 d-flex align-items-center justify-content-end gap-2'>
                    <MatchesPageHeader title={''} startDate={startDate} setStartDate={handleSetStartDate} />
                    <div>
                        {
                            competition
                            &&
                            <AsyncSelect
                                id="coachID"
                                className="form-control"
                                placeholder="Select season"
                                name='season_id'
                                value={selectedSeason}
                                onChange={(v) => handleSetSelectedSeason(v)}
                                defaultOptions
                                loadOptions={(q: any) => loadOptions(q)}
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