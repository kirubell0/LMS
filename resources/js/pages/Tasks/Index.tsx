import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { Calendar, CheckCircle, CheckCircle2, ChevronLeft, ChevronRight, List, Pencil, Plus, Search, Trash2, XCircle, Download, Trash,Eye } from 'lucide-react';
import { useEffect, useState, FormEvent } from 'react';
import { useRef } from 'react';
// import EditorX  from '@/pages/editor-x'
interface Task {
    id: number;
    ref_no: string | null;
    to: string;
    subject: string | null;
    body: string;
    cc:string | null;
    is_completed: boolean;
    date: string ;
    list_id: number;
    list: {
        id: number;
        title: string;
    };
    approved_by: string;
    approved_position: string;
}

interface List {
    id: number;
    title: string;
}

interface Props {
    tasks: {
        data: Task[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    lists: List[];
    filters: {
        search: string;
        filter: string;
    };
    flash?: {
        success?: string;
        error?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Letters',
        href: '/tasks',
    },
];

export default function TasksIndex({ tasks, lists, filters, flash }: Props) {
    const [isOpen, setIsOpen] = useState(false);
    const [editingTask, setEditingTask] = useState<Task | null>(null);
    const [showToast, setShowToast] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [toastType, setToastType] = useState<'success' | 'error'>('success');
    const [searchTerm, setSearchTerm] = useState(filters.search);
    const [completionFilter, setCompletionFilter] = useState<'all' | 'completed' | 'pending'>(filters.filter as 'all' | 'completed' | 'pending');

    useEffect(() => {
        if (flash?.success) {
            setToastMessage(flash.success);
            setToastType('success');
            setShowToast(true);
        } else if (flash?.error) {
            setToastMessage(flash.error);
            setToastType('error');
            setShowToast(true);
        }
    }, [flash]);

    useEffect(() => {
        if (showToast) {
            const timer = setTimeout(() => {
                setShowToast(false);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [showToast]);

    const {
        data,
        setData,
        post,
        put,
        processing,
        reset,
        delete: destroy,
    } = useForm({
        ref_no: '',
        title: '',
        to: '',
        subject: '',
        body: '',
        cc: '',
        approved_by: '',
        approved_position: '',
        date: '',
        list_id: '',
        is_completed: false as boolean,
    });

    const handleSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (editingTask) {
            put(route('tasks.update', editingTask.id), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                    setEditingTask(null);
                },
            });
        } else {
            post(route('tasks.store'), {
                onSuccess: () => {
                    setIsOpen(false);
                    reset();
                },
            });
        }
    };

    const handleEdit = (task: Task) => {
        setEditingTask(task);
        setData({
            ref_no: task.ref_no ?? '',
            title: '', // You may want to set this to a relevant value if available
            to: task.to,
            subject: task.subject ?? '',
            body: task.body ?? '',
            cc: task.cc ?? '',
            approved_by: task.approved_by ?? '',
            approved_position: task.approved_position ?? '',
            date: task.date ?? '',
            list_id: task.list_id.toString(),
            is_completed: task.is_completed,
        });
        setIsOpen(true);
    };

  const handlePrint = (taskId: number) => {
    window.open(route('tasks.printPDF', taskId));
};

const printFrameRef = useRef<HTMLIFrameElement>(null);

const handlePrintInPlace = (taskId: number) => {
    const pdfUrl = route('tasks.printPDF', taskId);
    if (printFrameRef.current) {
        printFrameRef.current.src = pdfUrl;
        printFrameRef.current.onload = () => {
            printFrameRef.current?.contentWindow?.print();
        };
    }
};

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        router.get(
            route('tasks.index'),
            {
                search: searchTerm,
                filter: completionFilter,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleFilterChange = (value: 'all' | 'completed' | 'pending') => {
        setCompletionFilter(value);
        router.get(
            route('tasks.index'),
            {
                search: searchTerm,
                filter: value,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handlePageChange = (page: number) => {
        router.get(
            route('tasks.index'),
            {
                page,
                search: searchTerm,
                filter: completionFilter,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tasks" />
            <div className="from-background to-muted/20 flex h-full flex-1 flex-col gap-6 rounded-xl bg-gradient-to-br p-6">
                {showToast && (
                    <div
                        className={`fixed top-4 right-4 z-50 flex items-center gap-2 rounded-lg p-4 shadow-lg ${
                            toastType === 'success' ? 'bg-green-500' : 'bg-red-500'
                        } animate-in fade-in slide-in-from-top-5 text-white`}
                    >
                        {toastType === 'success' ? <CheckCircle2 className="h-5 w-5" /> : <XCircle className="h-5 w-5" />}
                        <span>{toastMessage}</span>

                    </div>
                )}

                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Letters</h1>
                        <p className="text-muted-foreground mt-1">Manage your letters and stay organized</p>
                    </div>
                    <Dialog open={isOpen} onOpenChange={setIsOpen}>
                        <DialogTrigger>
                            <Button className="bg-primary hover:bg-primary/90 text-white shadow-lg dark:text-black">
                                <Plus className="mr-2 h-4 w-4" />
                                Create letter
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="sm:max-w-[979px] mb-4">
                            <DialogHeader>
                                <DialogTitle className="text-xl">{editingTask ? 'Edit Letter' : 'Create New Letter'}</DialogTitle>
                            </DialogHeader>
                            {/* <EditorX />   */}
                            <form onSubmit={handleSubmit} className="space-y-4 scroll-smooth ">
                                <div className="space-y-2">
                                    <Label htmlFor="ref_no">Ref. No</Label>
                                    <Input
                                        id="ref_no"
                                        value={data.ref_no}
                                        onChange={(e) => setData('ref_no', e.target.value)}
                                        required
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>{' '}
                                <div className="space-y-2">
                                    <Label htmlFor="due_date">Date :</Label>
                                    <Input
                                        id="due_date"
                                        type="text"
                                        value={data.date}
                                        onChange={(e) => setData('date', e.target.value)}
                                        className="focus:ring-primary focus:ring-2 dark:text-white"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="to">To : </Label>
                                    <Input
                                        id="to"
                                        value={data.to}
                                        onChange={(e) => setData('to', e.target.value)}
                                        required
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="subject">Subject :</Label>
                                    <Input
                                        id="subject"
                                        value={data.subject}
                                        onChange={(e) => setData('subject', e.target.value)}
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="description">Body</Label>
                                    <Textarea
                                        id="description"
                                        value={data.body}
                                        onChange={(e) => setData('body', e.target.value)}
                                        required
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="list_id">Type of letter</Label>
                                    <Select value={data.list_id} onValueChange={(value:any) => setData('list_id', value)}>
                                        <SelectTrigger className="focus:ring-primary focus:ring-2">
                                            <SelectValue placeholder="Select a list" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {lists.map((list) => (
                                                <SelectItem key={list.id} value={list.id.toString()}>
                                                    {list.title}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="cc">CC</Label>
                                    <Input
                                        id="cc"
                                        value={data.cc}
                                        onChange={(e) => setData('cc', e.target.value)}
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="approved_by">Approved by</Label>
                                    <Input
                                        id="approved_by"
                                        value={data.approved_by}
                                        onChange={(e) => setData('approved_by', e.target.value)}
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="approved_positions">Approved Positions</Label>
                                    <Input
                                        id="approved_positions"
                                        value={data.approved_position}
                                        onChange={(e) => setData('approved_position', e.target.value)}
                                        className="focus:ring-primary focus:ring-2"
                                    />
                                </div>
                                <div className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="is_completed"
                                        checked={data.is_completed}
                                        onChange={(e) => setData('is_completed', e.target.checked)}
                                        className="focus:ring-primary h-4 w-4 rounded border-gray-300 focus:ring-2"
                                    />
                                    <Label htmlFor="is_completed">Completed</Label>
                                </div>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-primary hover:bg-primary/90 w-full text-white shadow-lg dark:text-black"
                                >
                                    {editingTask ? 'Update' : 'Create'}
                                </Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
                <div className="mb-4 flex gap-4">
                    <form onSubmit={handleSearch} className="relative flex-1">
                        <Search className="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform" />
                        <Input placeholder="Search letter..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="pl-10" />
                    </form>{' '}
                    <Select value={completionFilter} onValueChange={handleFilterChange}>
                        <SelectTrigger className="w-[180px]">
                            <SelectValue placeholder="Filter by status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Letters</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                {/* List Table */}
                <div className="rounded-md border">
                    <div className="relative w-full overflow-auto">
                        <table className="w-full caption-bottom text-sm">
                            <thead className="[&_tr]:border-b">
                                <tr className="hover:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors pl-40">
                                    <th className="text-muted-foreground h-12 px-4 text-left align-middle font-medium">Reference number</th>
                                    <th className="text-muted-foreground h-12 px-4 text-left align-middle font-medium"></th>
                                    <th className="text-muted-foreground h-12 px-4 text-left align-middle font-medium">Body</th>
                                    <th className="text-muted-foreground h-12 px-4 text-left align-middle font-medium">Type of Letter</th>
                                    <th className="text-muted-foreground h-12 px-4 text-left align-middle font-medium">Date</th>
                                    <th className="text-muted-foreground h-12 px-4 text-right align-middle font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="[&_tr:last-child]:border-0">
                                {tasks.data.map((task) => (
                                    <tr key={task.id} className="hover:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors text-black dark:text-white">
                                        <td className="p-4 font-medium">{task.ref_no}</td>
                                        <td className="p-4 align-middle font-medium">{task.subject}</td>
                                        {/* <td className="p-4 align-middle font-medium">{task.subject}</td> */}
                                        <td className="max-w-[200px] truncate p-4 align-middle">{task.body || 'No description'}</td>
                                        <td className="p-4 align-middle">
                                            <div className="flex items-center gap-2">
                                                <List className="text-muted-foreground h-4 w-4" />
                                                {task.list.title}
                                            </div>
                                        </td>
                                        <td className="p-4 align-middle">
                                            {task.date ? (
                                                <div className="flex items-center gap-2">
                                                    <Calendar className="text-muted-foreground h-4 w-4" />
                                                    {new Date(task.date).toLocaleDateString()}
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">No due date</span>
                                            )}
                                        </td>
                                        <td className="p-4 align-middle">
                                            {task.is_completed ? (
                                                <div className="flex items-center gap-2 text-green-500">
                                                    <CheckCircle className="h-4 w-4" />
                                                    <span>Completed</span>
                                                </div>
                                            ) : (
                                                <div className="flex items-center gap-2 text-yellow-500">
                                                    <span>Pending</span>
                                                </div>
                                            )}
                                        </td>
                                        <iframe ref={printFrameRef} style={{ display: 'none' }} title="print-frame" />
                                        <td className="p-4 text-right align-middle">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>  handlePrintInPlace(task.id)}
                                                    className="hover:bg-primary/10 hover:text-primary"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Button>{' '}
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() =>handlePrintInPlace(task.id)}
                                                    className="hover:bg-destructive/10 hover:text-black"
                                                >
                                                    <Download className="h-4 w-4" />
                                                </Button>
                                                {/* <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handlePrint(task.id)}
                                                    className="hover:bg-destructive/10 hover:text-black"
                                                >
                                                    <Trash className="h-4 w-4" />
                                                </Button> */}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {tasks.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="text-muted-foreground p-4 text-center">
                                            No tasks foreground
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
                {/* Pagination */}
                <div className="flex items-center justify-between px-2">
                    <div className="text-muted-foreground text-sm">
                        Showing {tasks.from} to {tasks.to} of {tasks.total} results
                    </div>
                    <div className="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => handlePageChange(tasks.current_page - 1)}
                            disabled={tasks.current_page === 1}
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <div className="flex items-center space-x-1">
                            {Array.from({ length: tasks.last_page }, (_, i) => i + 1).map((page) => (
                                <Button
                                    key={page}
                                    variant={page === tasks.current_page ? 'default' : 'outline'}
                                    size="icon"
                                    onClick={() => handlePageChange(page)}
                                >
                                    {page}
                                </Button>
                            ))}
                        </div>
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => handlePageChange(tasks.current_page + 1)}
                            disabled={tasks.current_page === tasks.last_page}
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
