import useAxios from './useAxios';

type Props = {}

const useListDependsOn = () => {

    const { get } = useAxios()

    const fetchSelectData = (url: string, setDataFunction?: (data: []) => any) => {
        get(url).then((results) => {
            
            if (!!(results && setDataFunction)) {
                setDataFunction(results.data);
            }
        });
    };

    const handleSelectChange = async (job: { uri: string, fn: (data: []) => any }, dependencies: object[]) => {
        let updatedUri = job.uri;

        dependencies.forEach((dependency: any) => {

            const [name, selectedValue] = dependency

            updatedUri += `&${name}=${selectedValue?.id || 0}`;

        });

        fetchSelectData(updatedUri, job.fn);

    };

    return { fetchSelectData, handleSelectChange };

}

export default useListDependsOn