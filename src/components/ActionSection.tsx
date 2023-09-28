import { Dropdown } from 'flowbite-react';
import React from 'react'
import { NavLink } from 'react-router-dom';

type Props = {
    action: any
    row: any
    baseUri: any
    singleUri: any
    handleView: any
    handleEdit: any
    handleDelete: any
    resolve: (...args: any[]) => string
}

function ActionSection({ action, row, baseUri, singleUri, handleView, handleEdit, handleDelete, resolve }: Props) {
    return (
        <div>
            {action.mode == 'dropdown' ?
                <Dropdown label="Actions" size={'sm'}>
                    {
                        action.view && <Dropdown.Item>
                            <NavLink to={resolve(baseUri, singleUri, row)}
                                className="w-full px-1 py-2 rounded"
                                onClick={e => {
                                    if (action.view === 'modal') {
                                        e.preventDefault(); handleView(row)
                                    }
                                }
                                }
                            >View
                            </NavLink>
                        </Dropdown.Item>
                    }
                    {
                        action.edit &&
                        <Dropdown.Item>
                            <NavLink to={resolve(baseUri, singleUri, row ) + `/edit`}
                                className="w-full px-1 py-2 rounded"
                                onClick={e => {
                                    if (action.edit === 'modal') {
                                        e.preventDefault(); handleEdit(row)
                                    }
                                }
                                }
                            >
                                Edit
                            </NavLink>
                        </Dropdown.Item>
                    }
                    {
                        action.delete &&
                        <Dropdown.Item>
                            <form action={resolve(baseUri, singleUri, row )}>
                                <button type='submit'
                                    className="w-full px-1 py-2 rounded"
                                    onClick={e => {
                                        e.preventDefault(); handleDelete(row)
                                    }
                                    }
                                >
                                    Delete
                                </button>
                            </form>
                        </Dropdown.Item>
                    }
                </Dropdown>

                :
                <div className="flex">
                    {
                        action.view &&
                        <NavLink to={resolve(baseUri, singleUri, row )}
                            className="px-4 py-2 mr-2 text-white bg-blue-500 hover:bg-blue-600 rounded"
                            onClick={e => {
                                if (action.view === 'modal') {
                                    e.preventDefault(); handleView(row)
                                }
                            }
                            }
                        >
                            View
                        </NavLink>
                    }
                    {
                        action.edit &&
                        <NavLink to={resolve(baseUri, singleUri, row ) + `/edit`}
                            className="px-4 py-2 mr-2 text-white bg-green-500 hover:bg-green-600 rounded"
                            onClick={e => {
                                if (action.edit === 'modal') {
                                    e.preventDefault(); handleEdit(row)
                                }
                            }
                            }
                        >
                            Edit
                        </NavLink>
                    }
                    {
                        action.delete &&
                        <form action={resolve(baseUri, singleUri, row )}>
                            <button type='submit'
                                className="px-4 py-2 text-white bg-red-500 hover:bg-red-600 rounded"
                                onClick={e => {
                                    e.preventDefault(); handleDelete(row)
                                }
                                }
                            >
                                Delete
                            </button>
                        </form>
                    }
                </div>
            }
        </div>
    )
}

export default ActionSection