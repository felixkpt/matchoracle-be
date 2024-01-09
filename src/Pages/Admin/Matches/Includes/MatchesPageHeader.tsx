import FormatDate from "@/utils/FormatDate";
import Flatpickr from "react-flatpickr";

type Props = {
    title: string;
    fromToDates: any
    setFromToDates: any
};

const MatchesPageHeader = ({ title, fromToDates, setFromToDates }: Props) => {

    function handleSetDate(selectedDates: Date[]) {
        let from_date = FormatDate.YYYYMMDD(selectedDates[0])
        let to_date = ''
        if (selectedDates[1]) {
            to_date = FormatDate.YYYYMMDD(selectedDates[1])
        }
        setFromToDates([from_date, to_date])
    }

    return (
        <div className='header-title shadow-sm p-2 rounded d-flex justify-content-between'>
            <h3 className='heading'>{title}</h3>
            <div>
                {
                    typeof setFromToDates === 'function' &&
                    <div>
                        <Flatpickr
                            defaultValue={fromToDates[0]}
                            data-mode="range"
                            data-date-format="Y-m-d"
                            onChange={(selectedDates: Date[]) => handleSetDate(selectedDates)}
                        />
                    </div>
                }
            </div>
        </div>
    )
}

export default MatchesPageHeader