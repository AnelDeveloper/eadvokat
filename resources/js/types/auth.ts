export type User = {
    id: number;
    name: string;
    email: string;
    office: string;
};

export type Auth = {
    user: User | null;
};
