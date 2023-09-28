import Select, { PropsValue } from 'react-select';
import Str from '@/utils/Str';
import { useEffect, useState } from "react";
import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces';

interface RenderAsyncSelectProps {
    current_key: string;
    currentData: {};
    isMulti?: boolean;
    list_sources?: { [key: string]: () => Promise<ListSourceInterface[]> };
}

const RenderAsyncSelect = ({ list_sources, current_key, currentData, isMulti = false }: RenderAsyncSelectProps) => {

    async function getOptions(current_key: string, rawSelected: PropsValue<object> | PropsValue<object[]> | undefined) {

        if (!list_sources) return {};

        const fn = Str.camel(current_key);

        // Type assertion to specify that list_sources[fn] is a function returning Promise<any>
        const listSourceFn = list_sources[fn] as (() => Promise<any>);

        if (typeof listSourceFn === 'function') {
            const options = await listSourceFn();

            let selected = rawSelected;

            if (Array.isArray(rawSelected)) {
                selected = options.filter((option: any) => rawSelected.some((selectedItem: any) => String(selectedItem.id) === String(option.id)));
            } else if (typeof rawSelected === 'number' || typeof rawSelected === 'string') {
                selected = options.find((option: any) => String(option.id) === String(rawSelected));
            }

            return { options, selected };
        } else {
            throw new Error(`Function '${fn}' not found in list_sources.`);
        }
    }

    const [options, setOptions] = useState([]);
    const [selected, setSelected] = useState<PropsValue<object> | undefined>();

    useEffect(() => {

        if (current_key) {

            async function fetchData() {
                const currentValue = currentData || (isMulti ? [] : '');
                const { options: fetchedOptions, selected: fetchedSelected } = await getOptions(current_key, currentValue);

                setOptions(fetchedOptions);
                setSelected(fetchedSelected);
            }

            fetchData();
        }

    }, [current_key, currentData, isMulti]);

    return (
        <Select
            id={current_key}
            className="form-control"
            name={
                isMulti && Array.isArray(selected) && selected.length > 0
                    ? `${current_key}[]`
                    : current_key
            }
            key={current_key}
            defaultValue={selected}
            value={selected}
            isMulti={isMulti}
            options={options}
            onChange={(val) => setSelected(val)}
            getOptionValue={(option) => `${option['id']}`}
            getOptionLabel={(option) => `${option['name']}`}
        />
    );
};

export default RenderAsyncSelect