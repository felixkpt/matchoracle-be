import FormatDate from "@/utils/FormatDate";
import DatePicker from "react-datepicker";

type Props = {
    title: string;
    startDate: any
    setStartDate: any
};

const MatchesPageHeader = ({ title, startDate, setStartDate }: Props) => {

    return (
        <div className='header-title shadow-sm p-2 rounded d-flex justify-content-between'>
            <h3 className='heading'>{title}</h3>
            <div>
                {
                    typeof setStartDate === 'function' &&
                    <div>
                        <DatePicker className="form-control z-index-50"
                            placeholderText='Choose a date'
                            selected={startDate ? new Date(startDate) : null}
                            onChange={(date: Date) => setStartDate(FormatDate.YYYYMMDD(date))} />
                        {/* <DatePicker
                            selected={new Date(startDate)}
                            onChange={(date: Date) => setStartDate(date)}
                            includeDates={[new Date()]}
                            inline
                        /> */}
                    </div>
                }
            </div>
        </div>
    )
}

export default MatchesPageHeader