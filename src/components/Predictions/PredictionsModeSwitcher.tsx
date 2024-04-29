import Select from 'react-select';

type Props = {
    predictionMode: any
    predictionModes: any
    setPredictionMode: any
    title?: any
}

const PredictionsModeSwitcher = ({ predictionMode, predictionModes, setPredictionMode, title }: Props) => {
    return (
        <div className='d-flex gap-1 align-items-center justify-content-center shadow-sm px-2 rounded'>
            <div className='text-nowrap'>{title || 'Tips Mode'}:</div>
            <Select
                className="tips-mode-input form-control border-0"
                classNamePrefix="select"
                defaultValue={predictionMode || null}
                isDisabled={false}
                isLoading={false}
                isClearable={false}
                isSearchable={false}
                placeholder="Select predictions mode"
                name='prediction_mode_id'
                options={predictionModes || []}
                onChange={(v: any) => setPredictionMode(v)}
                getOptionValue={(option: any) => `${option['id']}`}
                getOptionLabel={(option: any) => option['name']}
            />
        </div>
    )
}

export default PredictionsModeSwitcher