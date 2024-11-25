import { Icon } from "@iconify/react/dist/iconify.js";

interface RenderStatBlockProps {
    label: string;
    customCount: number | string;
    allTimeCount: number | string;
    icon: string;
    colorClass: string;
}

const RenderStatBlock: React.FC<RenderStatBlockProps> = ({ label, customCount, allTimeCount, icon, colorClass }) => (
    <div className={`w-100`} style={{ fontSize: '80%' }}>
        <div className={`row overflow-auto shadow-sm p-2 rounded justify-content-between ${colorClass}`}>
            <div className='col-6'>
                <div className="d-flex align-items-center justify-content-start text-start gap-2 ">
                    <Icon width={'1.2rem'} icon={icon} />
                    <span>{label}</span>
                </div>
            </div>
            <div className="col-6">
                <div className="d-flex gap-3">
                    <div className="col-6 text-center">{customCount || 0}</div>
                    <div className="col-6 text-center">{allTimeCount || 0}</div>
                </div>
            </div>
        </div>
    </div>
);

export default RenderStatBlock;
