export function submissionFailed(page: { props: { error?: unknown } }): boolean {
    return page.props.error != null;
}
