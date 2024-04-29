import React from 'react'

type Props = {}

const Aside = ({ itemList }: Props) => {
    
    return (
        <div>
            {
                itemList.map((child, i) => {

                    const folder = child.folder
                    const currentId = Str.slug((folder).replace(/^\//, ''));

                    return <Draggable key={currentId} draggableId={currentId} index={i} className='draggable d-flex'>
                        {(provided) => (
                            <div
                                className="item-container"
                                ref={provided.innerRef}
                                {...provided.dragHandleProps}
                                {...provided.draggableProps}
                            >
                                <button className={`nav-link text-start border w-100 ${i === 0 ? 'show active' : ''}`} id={`v-pills-${currentId}-tab`} data-bs-toggle="pill" data-bs-target={`#v-pills-${currentId}`} type="button" role="tab" aria-controls={`v-pills-${currentId}`} aria-selected="true">{Str.title(folder)}</button>
                            </div>
                        )}
                    </Draggable>

                    // <div key={currentId} className='draggable d-flex'>
                    //                     <button className={`nav-link text-start border w-100 ${i === 0 ? 'show active' : ''}`} id={`v-pills-${currentId}-tab`} data-bs-toggle="pill" data-bs-target={`#v-pills-${currentId}`} type="button" role="tab" aria-controls={`v-pills-${currentId}`} aria-selected="true">{Str.title(folder)}</button>
                    //                   </div>
                })
            }
        </div>
    )
}

export default Aside