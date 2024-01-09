import { ReactNode } from "react";

export interface UserInterface {
    [x: string]: ReactNode;
    id: number;
    name: string;
    email: string;
    avatar?: string
    fileAccessToken: string | null
}

export interface UsersApiResponseInterface {
    users: UserInterface[];
}

export interface UserApiResponseInterface {
    user: UserInterface;
}