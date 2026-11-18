import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/react';

export function ConfirmDeleteDialog({
    open,
    title,
    description,
    destroyUrl,
    onOpenChange,
}: {
    open: boolean;
    title: string;
    description: string;
    destroyUrl: string | null;
    onOpenChange: (open: boolean) => void;
}) {
    const remove = useForm({});

    function confirm() {
        if (destroyUrl === null) {
            return;
        }

        remove.delete(destroyUrl, {
            onFinish: () => onOpenChange(false),
        });
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose render={<Button type="button" variant="outline" />}>Cancel</DialogClose>
                    <Button type="button" variant="destructive" disabled={remove.processing} onClick={confirm}>
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
