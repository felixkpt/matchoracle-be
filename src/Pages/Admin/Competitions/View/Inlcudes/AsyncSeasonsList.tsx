import useAxios from '@/hooks/useAxios';
import { CompetitionTabInterface } from '@/interfaces/FootballInterface';
import Str from '@/utils/Str';
import AsyncSelect from 'react-select/async';


const AsyncSeasonsList = ({ record, selectedSeason, setSelectedSeason, useDate }: CompetitionTabInterface) => {

    const competition = record

    const { get, loading } = useAxios()

    const loadOptions = async (q: string) => {

        if (competition) {

            const currentValue = selectedSeason;

            const { data: fetchedOptions } = await get(`/admin/seasons?all=1&competition_id=${competition.id}&q=${q}`);
            if (currentValue) {

                const item = fetchedOptions.find((itm: any) => itm.id === currentValue.id)
                setSelectedSeason(item)
            } else if(!useDate) {
                setSelectedSeason(fetchedOptions[0])
            }
            // Include the existing record's option in fetchedOptions if not already present
            if (currentValue && !fetchedOptions.some((option: any) => option.id === currentValue.id)) {
                fetchedOptions.push(currentValue);
            }

            return fetchedOptions
        }
    }

    return (
        <div>
            <AsyncSelect
                key={selectedSeason?.id}
                id="coachID"
                className="form-control"
                placeholder="Select season"
                name='season_id'
                value={selectedSeason}
                onChange={(v) => setSelectedSeason(v)}
                defaultOptions
                loadOptions={(q: any) => loadOptions(q)}
                getOptionValue={(option: any) => `${option['id']}`}
                getOptionLabel={(option: any) => `${Str.before(option['start_date'], '-')} / ${Str.before(option['end_date'], '-')}`}
            />
        </div>
    )
}

export default AsyncSeasonsList