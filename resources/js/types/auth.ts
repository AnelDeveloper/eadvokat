export type User = {
    id: number;
    name: string;
    email: string;
    office: string;
    /** Days of trial access left (today included); null when there is no trial. */
    trialDaysLeft: number | null;
};

export type Auth = {
    user: User | null;
};
