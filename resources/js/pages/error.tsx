import { Button } from "@/components/ui/button";
import { dashboard } from "@/routes";
import { useLang } from "@erag/lang-sync-inertia/react";
import { Head, Link } from "@inertiajs/react";
import { Copy } from "lucide-react";
import { useState } from "react";

type Props = {
    status: 403 | 404 | 429 | 500 | 503;
    requestId: string | null;
};

export default function Error({ status, requestId }: Props) {
    const { __ } = useLang();
    const [copied, setCopied] = useState(false);

    const title = __(`pages/errors.${status}.title`);
    const description = __(`pages/errors.${status}.description`);

    const copyRequestId = async () => {
        if (!requestId) return;

        await navigator.clipboard.writeText(requestId);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <>
            <Head title={__(`${status} - ${title}`)} />

            <main className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background px-6 text-center">
                <img src="/images/errors/placeholder.svg" alt="" className="h-48 w-48 dark:invert" />

                <div className="space-y-2">
                    <h1 className="text-2xl font-semibold text-foreground">
                        {title}
                    </h1>
                    <p className="max-w-md text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>

                {requestId && (
                    <div className="flex items-center gap-2 rounded-md border bg-muted/50 px-3 py-2 text-xs text-muted-foreground">
                        <span>
                            {__('pages/errors.request_id_label')}: {requestId}
                        </span>

                        <button
                            type="button"
                            onClick={copyRequestId}
                            aria-label={__('pages/errors.copy_request_id')}
                            className="rounded p-1 hover:bg-muted"
                        >
                            <Copy className="size-3.5" aria-hidden="true" />
                        </button>

                        <span role="status" className="sr-only">
                            {copied
                                ? __('pages/errors.request_id_copied')
                                : ''}
                        </span>
                    </div>
                )}

                <Button asChild>
                    <Link href={dashboard()}>
                        {__('pages/errors.back_to_dashboard')}
                    </Link>
                </Button>
            </main>
        </>
    );
}
