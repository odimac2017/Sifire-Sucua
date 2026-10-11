export default function RiskCard({
    title,
    icon: Icon,
    color = "blue",
    active,
    onToggle,
    children
}) {
    return (
        <div
            onMouseDown={(e) => {
                e.preventDefault();
                e.stopPropagation();
                onToggle();
            }}
            className={`w-full border rounded-xl p-4 space-y-4 cursor-pointer transition-all duration-200
                ${
                    active
                        ? `border-${color}-500 bg-${color}-50 dark:bg-${color}-900/20`
                        : "hover:bg-muted"
                }
            `}
        >
            <div className="flex items-center gap-2">
                {Icon && <Icon className={`w-5 h-5 text-${color}-500`} />}
                <span className="font-medium">{title}</span>
            </div>

            <div onClick={(e) => e.stopPropagation()}>
                {children}
            </div>
        </div>
    );
}